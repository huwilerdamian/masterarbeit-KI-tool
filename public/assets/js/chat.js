$(function () {
  const $list = $('#chat-list');
  const $form = $('#chat-form');
  const $message = $('#message');
  const $attachBtn = $('#attach-btn');
  const $attachInput = $('#attach-input');
  const $fileSelected = $('#file-selected');
  const $filePreview = $('#file-preview');
  const taskId = window.__TASK_ID__;
  const scrollToBottom = () => {
    $list.scrollTop($list.prop('scrollHeight'));
  };

  scrollToBottom();

  $attachBtn.on('click', function () {
    $attachInput.trigger('click');
  });

  $attachInput.on('change', function () {
    const file = $attachInput[0] && $attachInput[0].files[0] ? $attachInput[0].files[0] : null;
    if (file) {
      $fileSelected.text(`Ausgewählt: ${file.name}`);
      if (file.type && file.type.startsWith('image/')) {
        const url = URL.createObjectURL(file);
        $filePreview.html(`<img class="chat-preview-image" src="${url}" alt="Vorschau">`);
      } else {
        $filePreview.html(`<div class="chat-preview-file">Datei: ${$('<div>').text(file.name).html()}</div>`);
      }
    } else {
      $fileSelected.text('');
      $filePreview.empty();
    }
  });

  $form.on('submit', async function (e) {
    e.preventDefault();
    const text = $message.val().trim();
    const file = $attachInput[0] && $attachInput[0].files[0] ? $attachInput[0].files[0] : null;
    if (!text && !file) {
      alert('Bitte eine Nachricht eingeben.');
      return;
    }

    const isImage = file && file.type && file.type.startsWith('image/');
    const fileUrl = file ? URL.createObjectURL(file) : '';
    const previewUrl = isImage ? fileUrl : '';
    const fileNameHtml = file ? $('<div>').text(file.name).html() : '';
    const messageHtml = $('<div>').text(text).html();
    const contentHtml = text ? `<div class="chat-content">${messageHtml}</div>` : '';
    const attachmentHtml = previewUrl
      ? `<div class="chat-attachment mt-2"><a href="${fileUrl}" target="_blank" rel="noopener"><img class="chat-attachment-image" src="${previewUrl}" alt="Anhang"></a></div>`
      : (file
        ? `<div class="chat-attachment mt-2"><a class="chat-attachment-file" href="${fileUrl}" target="_blank" rel="noopener" download><svg class="icon icon-file" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9.5a2 2 0 0 0-.6-1.4l-4.5-4.5A2 2 0 0 0 12.5 3H7Zm6 1.5V8a1 1 0 0 0 1 1h3.5L13 4.5Z"/></svg><span class="chat-attachment-name">${fileNameHtml}</span></a></div>`
        : '');
    $list.append(
      `<div class="chat-message d-flex justify-content-end">` +
        `<div class="chat-bubble chat-user">` +
          contentHtml +
          attachmentHtml +
        `</div>` +
      `</div>`
    );
    scrollToBottom();
    $message.val('');

    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('message', text);
    if (file) {
      formData.append('file', file);
    }

    const res = await fetch('chat_message.php', {
      method: 'POST',
      body: formData,
    });

    const data = await res.json();
    if (!res.ok || !data.ok) {
      alert(data.error || 'Fehler beim Senden.');
      return;
    }

    if (data.reply) {
      $list.append(
        `<div class="chat-message d-flex justify-content-start">` +
          `<div class="chat-bubble chat-assistant">` +
            `<div class="chat-content">${$('<div>').text(data.reply).html()}</div>` +
          `</div>` +
        `</div>`
      );
      scrollToBottom();
    }

    if ($attachInput[0]) {
      $attachInput.val('');
    }
    $fileSelected.text('');
    $filePreview.empty();
  });

  $message.on('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      $form.trigger('submit');
    }
  });
});
