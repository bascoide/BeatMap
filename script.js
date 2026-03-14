var path = document.getElementById("tail");
if (path) {
  path.setAttribute(
    "d",
    "M89,315c2.2-15.2-23-13.2-21.6,4.8c1.7,22.3,24.4,22.1,42.5,9.1c10.8-7.8,15.3-1.8,19.1,1.1 c2.3,1.7,6.7,3.3,11-3",
  );
}
//var segments = path.pathSegList;
//segments.getItem(2).y = -10;

var targetX = 50;
var targetY = 50;
var currentX = 50;
var currentY = 50;
var easing = 0.08;
var frameId = null;

function updateBackgroundPosition() {
  currentX += (targetX - currentX) * easing;
  currentY += (targetY - currentY) * easing;

  document.body.style.setProperty("--bg-x", currentX.toFixed(2) + "%");
  document.body.style.setProperty("--bg-y", currentY.toFixed(2) + "%");

  if (
    Math.abs(targetX - currentX) > 0.02 ||
    Math.abs(targetY - currentY) > 0.02
  ) {
    frameId = requestAnimationFrame(updateBackgroundPosition);
  } else {
    frameId = null;
  }
}

function startBackgroundAnimation() {
  if (!frameId) {
    frameId = requestAnimationFrame(updateBackgroundPosition);
  }
}

document.addEventListener("mousemove", function (event) {
  var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
  var viewportHeight =
    window.innerHeight || document.documentElement.clientHeight;

  targetX = (event.clientX / viewportWidth) * 100;
  targetY = (event.clientY / viewportHeight) * 100;

  startBackgroundAnimation();
});

document.addEventListener("mouseleave", function () {
  targetX = 50;
  targetY = 50;
  startBackgroundAnimation();
});
