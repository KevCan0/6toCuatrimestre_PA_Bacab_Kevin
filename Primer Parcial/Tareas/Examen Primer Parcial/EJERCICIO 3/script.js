const URL_API = 'https://fakestoreapi.com/products';
const grid = document.getElementById('productos-grid');

fetch(URL_API)
    .then(response => response.json())
    .then(productos => {
        productos.forEach(producto => {
            const card = document.createElement('div');
            card.classList.add('producto-card');

            card.innerHTML = `
                <span class="categoria-badge">${producto.category}</span>
                <img src="${producto.image}" alt="${producto.title}">
                <div class="producto-titulo">${producto.title}</div>
                <div class="producto-precio">$${producto.price}</div>
            `;

            grid.appendChild(card);
        });
    })
    .catch(error => {
        console.error('Error al consumir la API:', error);
        grid.innerHTML = '<p>Hubo un error al cargar los productos.</p>';
    });