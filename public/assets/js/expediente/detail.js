(function () {
  const timeline = document.querySelector('#timeline-expediente');
  if (!timeline) return;

  timeline.querySelectorAll(':scope > div').forEach((item, index) => {
    item.classList.add('timeline-item');
    item.style.animationDelay = `${index * 80}ms`;
  });
})();
