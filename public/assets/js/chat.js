$(function () {
  const $list = $('#chat-list');
  const $form = $('#chat-form');
  const $message = $('#message');
  const taskId = window.__TASK_ID__;

  $form.on('submit', async function (e) {
    e.preventDefault();
    const text = $message.val().trim();
    if (!text) {
      alert('Bitte eine Nachricht eingeben.');
      return;
    }

    $list.append(`<p><strong>user:</strong> ${$('<div>').text(text).html()}</p>`);
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
      $list.append(`<p><strong>assistant:</strong> ${$('<div>').text(data.reply).html()}</p>`);
    }
  });
});
