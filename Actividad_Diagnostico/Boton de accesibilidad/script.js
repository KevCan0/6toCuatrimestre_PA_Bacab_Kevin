function togglePanel() {
  var panel = document.getElementById('panel');
  if (panel.style.display === 'none') {
    panel.style.display = 'block';
  } else {
    panel.style.display = 'none';
  }
}

var check1 = document.getElementById('check1');
var check2 = document.getElementById('check2');
var check3 = document.getElementById('check3');
var check4 = document.getElementById('check4');

check1.addEventListener('change', function() {
  if (this.checked) {
    document.body.classList.add('grande');
  } else {
    document.body.classList.remove('grande');
  }
});

check2.addEventListener('change', function() {
  if (this.checked) {
    document.body.classList.add('oscuro');
  } else {
    document.body.classList.remove('oscuro');
  }
});

check3.addEventListener('change', function() {
  if (this.checked) {
    document.body.classList.add('fuente');
  } else {
    document.body.classList.remove('fuente');
  }
});

check4.addEventListener('change', function() {
  if (this.checked) {
    document.body.classList.add('subrayado');
  } else {
    document.body.classList.remove('subrayado');
  }
});
