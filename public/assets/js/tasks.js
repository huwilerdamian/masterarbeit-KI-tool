$(function () {
  function updateProgressCircle() {
    let total = 0;
    let done = 0;

    $('.tasks.container .row[data-task-type]').each(function () {
      const $row = $(this);
      const type = Number($row.data('task-type'));
      if (type !== 1 && type !== 2) {
        return;
      }
      total += 1;
      if (Number($row.data('task-corrected')) === 1 && Number($row.data('task-state')) === 1) {
        done += 1;
      }
    });

    const percent = total > 0 ? Math.round((done / total) * 100) : 0;
    $('.tasks-progress-circle').css('--progress', percent);
    $('#tasks-progress-done').text(done);
    $('#tasks-progress-total').text(total);
    $('#tasks-progress-percent').text(percent);
  }

  function applyGroupFilter(group) {
    const $containers = $('.tasks.container[data-group]');
    if (group === 'all') {
      $containers.stop(true, true).slideDown(180);
      return;
    }
    $containers.each(function () {
      const $container = $(this);
      const current = String($container.data('group'));
      if (current === group) {
        $container.stop(true, true).slideDown(180);
      } else {
        $container.stop(true, true).slideUp(180);
      }
    });
  }

  $(document).on('click', '.tasks-filter-btn', function () {
    const $btn = $(this);
    if ($btn.hasClass('tasks-legend-toggle')) {
      return;
    }
    const group = String($btn.data('group'));
    $('.tasks-filter-btn').removeClass('is-active');
    $btn.addClass('is-active');
    applyGroupFilter(group);
  });

  $(document).on('click', '.tasks-legend-toggle', function () {
    const $btn = $(this);
    const $legend = $('.tasks-legend');
    const isVisible = $legend.is(':visible');
    $legend.stop(true, true).slideToggle(180);
    $btn.attr('aria-expanded', isVisible ? 'false' : 'true');
    $legend.attr('aria-hidden', isVisible ? 'true' : 'false');
    $btn.toggleClass('is-active', !isVisible);
  });

  updateProgressCircle();

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
    $btn.toggleClass('true', !!data.corrected);
    $btn.toggleClass('false', !data.corrected);

    const $row = $btn.closest('.row[data-task-type]');
    if ($row.length) {
      $row.data('task-corrected', data.corrected ? 1 : 0);
      updateProgressCircle();
    }
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
    $btn.toggleClass('true', data.state === 1);
    $btn.toggleClass('false', data.state !== 1);

    const $row = $btn.closest('.row[data-task-type]');
    if ($row.length) {
      $row.data('task-state', data.state);
      updateProgressCircle();
    }
  });

  $(document).on('click', 'a[show-solution]', async function () {
    const $link = $(this);
    const taskProgressId = Number($link.attr('show-solution'));

    if (!taskProgressId) {
      return;
    }

    try {
      const res = await fetch('update_solution_view_count.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ task_progress_id: taskProgressId }),
      });

      if (!res.ok) {
        return;
      }

      const data = await res.json();
      if (!data.ok) {
        return;
      }

      $link.find('.solution-view-count').text(Number(data.solution_view_count) || 0);
    } catch (error) {
      console.error('Fehler beim Aktualisieren des Lösungszählers.', error);
    }
  });
});
