$(function () {
  const $list = $('#chat-list');
  const $form = $('#chat-form');
  const $message = $('#message');
  const $attachBtn = $('#attach-btn');
  const $attachInput = $('#attach-input');
  const taskId = window.__TASK_ID__;
  const scrollToBottom = () => {
    $list.scrollTop($list.prop('scrollHeight'));
  };

  scrollToBottom();

  $attachBtn.on('click', function () {
    $attachInput.trigger('click');
  });

  $form.on('submit', async function (e) {
    e.preventDefault();
    const text = $message.val().trim();
    const file = $attachInput[0] && $attachInput[0].files[0] ? $attachInput[0].files[0] : null;
    if (!text && !file) {
      alert('Bitte eine Nachricht eingeben.');
      return;
    }

    const displayText = text || (file ? `Datei: ${file.name}` : '');
    $list.append(
      `<div class="chat-message d-flex justify-content-end">` +
        `<div class="chat-bubble chat-user">` +
          `<div class="chat-content">${$('<div>').text(displayText).html()}</div>` +
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
  });

  $message.on('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      $form.trigger('submit');
    }
  });
});
