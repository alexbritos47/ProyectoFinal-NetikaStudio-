
<?php



require_once __DIR__ . '/../database/Database.php';
// Incluye el archivo Database.php.
// __DIR__ representa la carpeta donde está este archivo.
// ../ significa subir una carpeta.
// De esta forma se puede utilizar la conexión a la base de datos.


class CategoriaRepository

// Esta clase se encarga de realizar las consultas de categorías en MySQL.
{
    private PDO $conexion;
    // Guarda la conexión a la base de datos.
    // private significa que solamente se puede utilizar dentro de esta clase.
    // PDO es el tipo de conexión utilizado para comunicarse con MySQL.


    public function __construct(PDO $conexion)
    // Constructor de la clase.
    // Se ejecuta automáticamente cuando se crea un objeto CategoriaRepository.
    // Recibe una conexión PDO como parámetro.
    {
        $this->conexion = $conexion;
        // Guarda la conexión recibida dentro del objeto.
        // $this representa al objeto actual.
        // -> permite acceder a una propiedad o método del objeto.
    }


    public function obtenerTodos(): array
    // Función que obtiene todas las categorías.
    // : array significa que la función devuelve un array.
    {
        $stmt = $this->conexion->query(
            "SELECT id_categoria, nombre, monto_cuota FROM CATEGORIA_SOCIO"
        );
        // Ejecuta una consulta SQL para obtener todas las categorías.
        // SELECT indica que queremos consultar datos.
        // Se obtienen id_categoria, nombre y monto_cuota.
        // FROM CATEGORIA_SOCIO indica la tabla de donde salen los datos.
        // query() ejecuta directamente la consulta.


        return $stmt->fetchAll();
        // fetchAll() obtiene todas las filas que devolvió la consulta.
        // return devuelve esos datos al lugar donde se llamó la función.
    }


    public function obtenerPorId(int $id): array|false
    // Busca una categoría utilizando su ID.
    // Recibe un número entero llamado $id.
    // Puede devolver un array si encuentra la categoría
    // o false si no encuentra ninguna.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_categoria, nombre, monto_cuota FROM CATEGORIA_SOCIO WHERE id_categoria = :id"
        );
        // Prepara una consulta SQL.
        // prepare() permite utilizar parámetros de forma segura.
        // :id es un parámetro que después será reemplazado por el ID recibido.
        // WHERE indica que queremos solamente la categoría cuyo ID coincida.


        $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza :id por el valor guardado en $id.


        return $stmt->fetch();
        // fetch() obtiene una sola fila del resultado.
        // Si encuentra la categoría, devuelve sus datos.
        // Si no encuentra nada, devuelve false.
    }


    public function crear(array $datos): int
    // Función para crear una nueva categoría.
    // Recibe los datos en un array.
    // : int significa que devuelve un número entero.
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO CATEGORIA_SOCIO (nombre, monto_cuota) VALUES (:nombre, :monto_cuota)"
        );
        // Prepara una consulta INSERT.
        // INSERT INTO sirve para agregar un nuevo registro.
        // Se van a guardar nombre y monto_cuota.
        // :nombre y :monto_cuota son parámetros.


        $stmt->execute([
            'nombre'      => $datos['nombre'],
            'monto_cuota' => $datos['monto_cuota'],
        ]);
        // Ejecuta el INSERT.
        // Toma los valores desde el array $datos.
        // $datos['nombre'] contiene el nombre de la categoría.
        // $datos['monto_cuota'] contiene el monto de la cuota.


        return (int) $this->conexion->lastInsertId();
        // lastInsertId() obtiene el ID que MySQL generó para el nuevo registro.
        // (int) convierte ese valor a un número entero.
        // return devuelve el ID creado.
    }


    public function actualizar(int $id, array $datos): bool
    // Función para actualizar una categoría.
    // Recibe el ID de la categoría y los datos nuevos.
    // : bool significa que devuelve true o false.
    {
        $campos = [];
        // Crea un array vacío.
        // Aquí se van a guardar los campos que queremos modificar.


        $parametros = ['id' => $id];
        // Crea un array de parámetros.
        // Guarda el ID que se utilizará en la consulta SQL.


        $permitidos = ['nombre', 'monto_cuota'];
        // Define cuáles son los campos que está permitido modificar.
        // En este caso solamente nombre y monto_cuota.


        foreach ($permitidos as $campo) {
            // Recorre uno por uno los campos permitidos.
            // $campo tendrá primero "nombre" y después "monto_cuota".

            if (array_key_exists($campo, $datos)) {
                // Comprueba si ese campo existe dentro del array $datos.

                $campos[] = "$campo = :$campo";
                // Agrega el campo a la lista de modificaciones.
                // Por ejemplo:
                // "nombre = :nombre"

                $parametros[$campo] = $datos[$campo];
                // Guarda el valor recibido dentro del array de parámetros.
            }
        }


        if (empty($campos)) {
            // Comprueba si no hay ningún campo para modificar.

            return false;
            // Si no hay nada para actualizar, termina la función
            // y devuelve false.
        }


        $sql = "UPDATE CATEGORIA_SOCIO SET " . implode(', ', $campos) . " WHERE id_categoria = :id";
        // Construye la consulta SQL de actualización.
        // UPDATE indica que queremos modificar datos.
        // SET indica qué campos se van a cambiar.
        // implode(', ', $campos) une los campos utilizando una coma.
        // WHERE id_categoria = :id indica qué categoría se debe modificar.


        $stmt = $this->conexion->prepare($sql);
        // Prepara la consulta SQL para ejecutarla de forma segura.


        return $stmt->execute($parametros);
        // Ejecuta la consulta utilizando los parámetros.
        // Devuelve true si la operación se ejecutó correctamente.
        // Devuelve false si ocurrió un problema.
    }


    public function eliminar(int $id): bool
    // Función para eliminar una categoría.
    // Recibe el ID de la categoría.
    // Devuelve true o false.
    {
        $stmt = $this->conexion->prepare(
            "DELETE FROM CATEGORIA_SOCIO WHERE id_categoria = :id"
        );
        // Prepara una consulta DELETE.
        // DELETE elimina un registro de la tabla.
        // WHERE indica qué categoría se quiere eliminar.


        return $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza :id por el ID recibido.
        // Devuelve true si la operación se ejecutó correctamente.
    }
}

