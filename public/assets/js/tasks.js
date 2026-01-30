$(function () {
  $(document).on('click', '.set-corrected', async function () {
    const $btn = $(this);
    const taskId = $btn.data('task-id');
    const current = $btn.data('corrected') === 1;
    const next = !current;

    const res = await fetch('update_corrected.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ task_id: taskId, corrected: next }),
    });

    if (!res.ok) {
      alert('Fehler beim Speichern.');
      return;
    }

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || 'Fehler beim Speichern.');
      return;
    }

    $btn.data('corrected', data.corrected ? 1 : 0);
    $btn.text(data.corrected ? 'Ja' : 'Nein');
  });

  $(document).on('click', '.set-state', async function () {
    const $btn = $(this);
    const taskId = $btn.data('task-id');
    const current = Number($btn.data('state')) === 1;
    const next = current ? 0 : 1;

    const res = await fetch('update_state.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ task_id: taskId, state: next }),
    });

    if (!res.ok) {
      alert('Fehler beim Speichern.');
      return;
    }

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || 'Fehler beim Speichern.');
      return;
    }

    $btn.data('state', data.state);
    $btn.text(data.state === 1 ? 'Ja' : 'Nein');
  });
});
