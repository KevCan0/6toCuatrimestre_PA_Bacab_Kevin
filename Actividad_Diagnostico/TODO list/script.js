let tareas = [];

function dibujar() {
    let lista = document.getElementById('tasksList');
    lista.innerHTML = '';
    
    let pendientes = 0;
    for (let i = 0; i < tareas.length; i++) {
        if (!tareas[i].hecho) pendientes++;
    }
    document.getElementById('pendingCount').textContent = pendientes;
    
    for (let i = 0; i < tareas.length; i++) {
        let t = tareas[i];
        let li = document.createElement('li');
        
        let check = document.createElement('input');
        check.type = 'checkbox';
        check.checked = t.hecho;
        check.onchange = function() { t.hecho = !t.hecho; dibujar(); };
        
        let texto = document.createElement('span');
        texto.textContent = t.nombre;
        if (t.hecho) texto.className = 'completed';
        texto.onclick = function() { t.hecho = !t.hecho; dibujar(); };
        
        let btn = document.createElement('button');
        btn.textContent = 'X';
        btn.onclick = function() { 
            tareas.splice(i, 1);
            dibujar();
        };
        
        li.appendChild(check);
        li.appendChild(texto);
        li.appendChild(btn);
        lista.appendChild(li);
    }
}

document.getElementById('addButton').onclick = function() {
    let input = document.getElementById('taskInput');
    if (input.value !== '') {
        tareas.push({ nombre: input.value, hecho: false });
        input.value = '';
        dibujar();
    }
};

document.getElementById('taskInput').onkeypress = function(e) {
    if (e.key === 'Enter') document.getElementById('addButton').click();
};

document.querySelectorAll('.filter-btn').forEach(function(btn) {
    btn.onclick = function() {
        document.querySelectorAll('.filter-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');
    };
});

dibujar();
