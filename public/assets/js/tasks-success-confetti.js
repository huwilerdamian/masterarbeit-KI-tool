(function ($) {
  const COLORS = ['#ff4d6d', '#ffd93d', '#6bcb77', '#4d96ff', '#845ec2', '#ff9f1c', '#2ec4b6'];
  const PARTICLE_COUNT = 140;
  const DURATION_MS = 6800;

  function randomBetween(min, max) {
    return Math.random() * (max - min) + min;
  }

  function easeInOutBell(progress) {
    return Math.sin(progress * Math.PI);
  }

  function createParticle($layer, bounds, xIndex, timeIndex, total) {
    const $particle = $('<span class="tasks-success-confetti-piece" aria-hidden="true"></span>');
    const size = randomBetween(8, 14);
    const slotWidth = bounds.width / Math.max(total, 1);
    const progress = total > 1 ? timeIndex / (total - 1) : 0.5;
    const density = easeInOutBell(progress);
    const left = Math.min(
      bounds.width - size,
      Math.max(0, xIndex * slotWidth + randomBetween(slotWidth * 0.15, slotWidth * 0.85))
    );
    const drift = randomBetween(-90, 90);
    const rotate = randomBetween(-540, 540);
    const delayBase = progress * (DURATION_MS * 0.55);
    const delaySpread = randomBetween(-120, 120);
    const delay = Math.max(0, delayBase + delaySpread - density * 260);
    const duration = randomBetween(4600, DURATION_MS + 600);
    const color = COLORS[Math.floor(Math.random() * COLORS.length)];
    const shape = Math.random() > 0.5 ? '999px' : '2px';

    $particle.css({
      position: 'absolute',
      top: '-24px',
      left: `${left}px`,
      width: `${size}px`,
      height: `${Math.max(6, size * randomBetween(0.55, 1.4))}px`,
      backgroundColor: color,
      borderRadius: shape,
      opacity: randomBetween(0.75, 1),
      pointerEvents: 'none',
      transform: `translate3d(0, 0, 0) rotate(${randomBetween(0, 180)}deg)`,
      animation: `tasksSuccessConfettiFall ${duration}ms cubic-bezier(.18,.72,.24,1) ${delay}ms forwards`,
      '--tasks-confetti-drift': `${drift}px`,
      '--tasks-confetti-rotate': `${rotate}deg`,
    });

    $layer.append($particle);
  }

  function ensureStyles() {
    if (document.getElementById('tasks-success-confetti-styles')) {
      return;
    }

    const style = document.createElement('style');
    style.id = 'tasks-success-confetti-styles';
    style.textContent = `
      .featherlight.tasks-success-lightbox {
        overflow: hidden;
      }

      .tasks-success-confetti-layer {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
        z-index: 9998;
      }

      .tasks-success-confetti-piece {
        will-change: transform, opacity;
      }

      @keyframes tasksSuccessConfettiFall {
        0% {
          transform: translate3d(0, -5vh, 0) rotate(0deg);
          opacity: 0;
        }

        8% {
          opacity: 1;
        }

        100% {
          transform: translate3d(var(--tasks-confetti-drift), 110vh, 0) rotate(var(--tasks-confetti-rotate));
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  }

  function launchConfetti($lightbox) {
    if (!$lightbox || !$lightbox.length || $lightbox.find('.tasks-success-confetti-layer').length) {
      return;
    }

    ensureStyles();

    const width = $lightbox.outerWidth() || window.innerWidth;
    const bounds = { width };

    const $layer = $('<div class="tasks-success-confetti-layer" aria-hidden="true"></div>');
    $lightbox.append($layer);

    const timeOrder = Array.from({ length: PARTICLE_COUNT }, (_, index) => index);
    for (let index = timeOrder.length - 1; index > 0; index -= 1) {
      const swapIndex = Math.floor(Math.random() * (index + 1));
      const current = timeOrder[index];
      timeOrder[index] = timeOrder[swapIndex];
      timeOrder[swapIndex] = current;
    }

    for (let index = 0; index < PARTICLE_COUNT; index += 1) {
      createParticle($layer, {
        width: Math.max(width, 1),
      }, index, timeOrder[index], PARTICLE_COUNT);
    }

    window.setTimeout(() => {
      $layer.remove();
    }, DURATION_MS + 1800);
  }

  $(document).on('tasks:success-popup-opened', function (_event, $lightbox) {
    launchConfetti($lightbox);
  });
}(window.jQuery));
