$(function () {
  const $list = $('#chat-list');
  const $form = $('#chat-form');
  const $message = $('#message');
  const $attachBtn = $('#attach-btn');
  const $attachInput = $('#attach-input');
  const $fileSelected = $('#file-selected');
  const $filePreview = $('#file-preview');
  const $sendBtn = $('.chat-send-btn');
  const $loading = $('#chat-loading');
  const taskId = window.__TASK_ID__;
  const scrollToBottom = () => {
    $list.scrollTop($list.prop('scrollHeight'));
  };

  const moveLoadingToEnd = () => {
    if ($loading.length) {
      $loading.appendTo($list);
    }
  };

  const setLoading = (isLoading) => {
    if (isLoading) {
      moveLoadingToEnd();
    }
    $loading.toggle(!!isLoading);
    $loading.attr('aria-hidden', isLoading ? 'false' : 'true');
    $sendBtn.prop('disabled', !!isLoading);
    if (isLoading) {
      scrollToBottom();
    }
  };

  // Markdown/LaTeX rendering helpers (marked + DOMPurify + KaTeX).
  const hasMarkdownRenderer = () => typeof window.marked !== 'undefined' && typeof window.DOMPurify !== 'undefined';

  // Decode HTML-escaped data-raw content back to the original text.
  const decodeHtml = (value) => {
    if (!value) {
      return '';
    }
    const textarea = document.createElement('textarea');
    textarea.innerHTML = value;
    return textarea.value;
  };

  // Normalize math block delimiters:
  // Converts plain block format:
  // [
  //   ...formula...
  // ]
  // into KaTeX-compatible \[ ... \] blocks.
  const normalizeMathDelimiters = (text) => {
    if (!text) {
      return '';
    }
    const normalized = text.replace(/\r\n?/g, '\n');
    const withBlocks = normalized.replace(/(^|\n)\s*\[\s*\n([\s\S]*?)\n\s*\](?=\n|$)/g, (match, start, inner) => {
      return `${start}\\[${inner}\\]`;
    });
    return withBlocks;
  };

  // Render Markdown to sanitized HTML (prevents XSS).
  const renderMarkdown = (text) => {
    if (!hasMarkdownRenderer()) {
      const escaped = $('<div>').text(text).html();
      return escaped.replace(/\n/g, '<br>');
    }

    if (typeof window.marked.setOptions === 'function') {
      window.marked.setOptions({
        breaks: true,
        gfm: true,
        headerIds: false,
        mangle: false,
      });
    }

    const rawHtml = window.marked.parse(text || '');
    return window.DOMPurify.sanitize(rawHtml, { USE_PROFILES: { html: true } });
  };

  // Extract LaTeX blocks before Markdown parsing so backslashes are preserved.
  const extractMath = (text) => {
    if (!text) {
      return { text: '', replacements: [] };
    }

    const replacements = [];
    let working = text;

    const addReplacement = (match) => {
      const token = `@@MATH_${replacements.length}@@`;
      replacements.push({ token, value: match });
      return token;
    };

    // Extract block and inline LaTeX so Markdown doesn't consume backslashes.
    working = working.replace(/\\\[[\s\S]*?\\\]/g, addReplacement);
    working = working.replace(/\\\([\s\S]*?\\\)/g, addReplacement);

    return { text: working, replacements };
  };

  // Restore extracted LaTeX back into the rendered HTML.
  const restoreMath = (html, replacements) => {
    if (!replacements.length) {
      return html;
    }
    let restored = html;
    replacements.forEach((item) => {
      restored = restored.replaceAll(item.token, item.value);
    });
    return restored;
  };

  // Render LaTeX to HTML using KaTeX auto-render.
  const renderMath = (element) => {
    if (typeof window.renderMathInElement !== 'function') {
      return;
    }

    window.renderMathInElement(element, {
      delimiters: [
        { left: '$$', right: '$$', display: true },
        { left: '\\[', right: '\\]', display: true },
        { left: '\\(', right: '\\)', display: false },
        { left: '$', right: '$', display: false },
      ],
      throwOnError: false,
    });
  };

  // Full render pipeline for a single message:
  // decode -> normalize math blocks -> extract math -> Markdown -> restore math -> KaTeX.
  const renderMessageContent = (element, text) => {
    const decodedText = decodeHtml(text);
    const normalizedText = normalizeMathDelimiters(decodedText);
    const extracted = extractMath(normalizedText);
    const html = renderMarkdown(extracted.text);
    const restoredHtml = restoreMath(html, extracted.replacements);
    element.innerHTML = restoredHtml;
    renderMath(element);
  };

  // Render all messages on initial page load (server-rendered HTML).
  const renderExistingMessages = () => {
    document.querySelectorAll('.chat-content[data-raw]').forEach((element) => {
      const text = element.getAttribute('data-raw') || '';
      renderMessageContent(element, text);
    });
  };

  renderExistingMessages();
  scrollToBottom();

  const updateSendState = () => {
    const hasText = $message.val().trim().length > 0;
    const hasFile = $attachInput[0] && $attachInput[0].files[0];
    $sendBtn.prop('disabled', !hasText && !hasFile);
  };

  updateSendState();

  $attachBtn.on('click', function () {
    $attachInput.trigger('click');
  });

  $attachInput.on('change', function () {
    const file = $attachInput[0] && $attachInput[0].files[0] ? $attachInput[0].files[0] : null;
    if (file) {
      const allowedTypes = ['image/jpeg', 'image/png'];
      if (!allowedTypes.includes(file.type)) {
        $fileSelected.text('Nur JPG oder PNG erlaubt.');
        $filePreview.empty();
        $attachInput.val('');
        updateSendState();
        return;
      }
      $fileSelected.text(`Ausgewählt: ${file.name}`);
      if (file.type && file.type.startsWith('image/')) {
        const url = URL.createObjectURL(file);
        $filePreview.html(`<img class="chat-preview-image" src="${url}" alt="Vorschau">`);
      } else {
        $filePreview.empty();
      }
    } else {
      $fileSelected.text('');
      $filePreview.empty();
    }
    updateSendState();
  });

  $form.on('submit', async function (e) {
    e.preventDefault();
    const text = $message.val().trim();
    const file = $attachInput[0] && $attachInput[0].files[0] ? $attachInput[0].files[0] : null;
    if (!text && !file) {
      return;
    }

    const isImage = file && file.type && file.type.startsWith('image/');
    const fileUrl = file ? URL.createObjectURL(file) : '';
    const previewUrl = isImage ? fileUrl : '';
    const contentHtml = text ? `<div class="chat-content" data-raw="${$('<div>').text(text).html()}"></div>` : '';
    const attachmentHtml = previewUrl
      ? `<div class="chat-attachment mt-2"><a href="${fileUrl}" target="_blank" rel="noopener"><img class="chat-attachment-image" src="${previewUrl}" alt="Anhang"></a></div>`
      : '';
    const $userMessage = $(
      `<div class="chat-message d-flex justify-content-end">` +
        `<div class="chat-bubble chat-user">` +
          contentHtml +
          attachmentHtml +
        `</div>` +
      `</div>`
    );
    $list.append($userMessage);
    moveLoadingToEnd();
    if (text) {
      const contentEl = $userMessage.find('.chat-content').get(0);
      if (contentEl) {
        renderMessageContent(contentEl, text);
      }
    }
    scrollToBottom();
    $message.val('');
    setLoading(true);

    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('message', text);
    if (file) {
      formData.append('file', file);
    }

    let data = null;
    try {
      const res = await fetch('chat_message.php', {
        method: 'POST',
        body: formData,
      });

      data = await res.json();
      if (!res.ok || !data.ok) {
        throw new Error(data && data.error ? data.error : 'Fehler beim Senden.');
      }
    } catch (err) {
      alert(err && err.message ? err.message : 'Fehler beim Senden.');
      setLoading(false);
      return;
    }

    if (data.reply) {
      const $assistantMessage = $(
        `<div class="chat-message d-flex justify-content-start">` +
          `<div class="chat-bubble chat-assistant">` +
            `<div class="chat-content" data-raw="${$('<div>').text(data.reply).html()}"></div>` +
          `</div>` +
        `</div>`
      );
      $list.append($assistantMessage);
      const contentEl = $assistantMessage.find('.chat-content').get(0);
      if (contentEl) {
        renderMessageContent(contentEl, data.reply);
      }
      scrollToBottom();
    }

    if ($attachInput[0]) {
      $attachInput.val('');
    }
    $fileSelected.text('');
    $filePreview.empty();
    updateSendState();
    setLoading(false);
  });

  $message.on('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      $form.trigger('submit');
    }
  });

  $message.on('input', function () {
    updateSendState();
  });
});
