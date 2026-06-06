rutas api

-- ventas, esta api es para los vendedores y la primera de la prueba
Post http: //127.0.0.1:8000/api/reports/sales

-- reportes, esto es para la segunda parte de la prueba
-- rutas 
GET	    http: //127.0.0.1:8000/api/productos	    Listar productos
GET	    http: //127.0.0.1:8000/api/productos/1	    Obtener producto por id
POST	http: //127.0.0.1:8000/api/productos	    Crear producto
PUT	    http: //127.0.0.1:8000/api/productos/1	    Actualizar producto por id
DELETE	http: //127.0.0.1:8000/api/productos/1	    Eliminar producto por id