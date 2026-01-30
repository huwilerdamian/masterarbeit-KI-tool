$(function () {
  const $list = $('#chat-list');
  const $form = $('#chat-form');
  const $message = $('#message');
  const taskId = window.__TASK_ID__;
  const scrollToBottom = () => {
    $list.scrollTop($list.prop('scrollHeight'));
  };

  scrollToBottom();

  $form.on('submit', async function (e) {
    e.preventDefault();
    const text = $message.val().trim();
    if (!text) {
      alert('Bitte eine Nachricht eingeben.');
      return;
    }

    $list.append(
      `<div class="chat-message d-flex justify-content-end">` +
        `<div class="chat-bubble chat-user">` +
          `<div class="chat-content">${$('<div>').text(text).html()}</div>` +
        `</div>` +
      `</div>`
    );
    scrollToBottom();
    $message.val('');

    const res = await fetch('chat_message.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ task_id: taskId, message: text }),
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
  });

  $message.on('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      $form.trigger('submit');
    }
  });
});
