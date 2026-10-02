
<?php


// Clase encargada de manejar la lógica de autenticación.
// Se ocupa de iniciar sesión, cerrar sesión
// y obtener el usuario que está actualmente conectado.
class AuthService
{
    // Guarda una instancia de UsuarioRepository.
    // El Repository es el encargado de buscar los usuarios en la base de datos.
    private UsuarioRepository $repository;


    // Constructor de la clase.
    // Recibe un UsuarioRepository.
    public function __construct(UsuarioRepository $repository)
    {
        // Guarda el Repository recibido.
        $this->repository = $repository;
    }


    /**
     * Valida las credenciales del usuario y abre la sesión.
     *
     * @throws RuntimeException
     * Se lanza una excepción si:
     * - El usuario no existe.
     * - El usuario está inactivo.
     * - La contraseña es incorrecta.
     */
    public function login(string $nombreUsuario, string $contrasena): array
    {
        // Busca en la base de datos al usuario
        // utilizando el nombre de usuario recibido.
        $usuario = $this->repository->obtenerPorNombreUsuario($nombreUsuario);


        // Comprueba si no se encontró ningún usuario.
        // Si no existe, lanza una excepción.
        if (!$usuario) {

            // RuntimeException representa un error durante la ejecución.
            // En este caso indica que las credenciales no son correctas.
            throw new RuntimeException('Usuario o contraseña incorrectos.');
        }


        // Comprueba si el estado del usuario no es "activo".
        if ($usuario['estado'] !== 'activo') {

            // Si está inactivo, no permite iniciar sesión.
            throw new RuntimeException('El usuario se encuentra inactivo.');
        }


        // Comprueba si la contraseña ingresada coincide
        // con el hash almacenado en la base de datos.
        //
        // password_verify() compara:
        // contraseña ingresada
        //        VS
        // contraseña hasheada almacenada.
        if (!password_verify($contrasena, $usuario['contrasena'])) {

            // Si no coinciden, se rechaza el inicio de sesión.
            throw new RuntimeException('Usuario o contraseña incorrectos.');
        }


        // Elimina la contraseña hasheada del arreglo.
        // Esto evita devolver o guardar innecesariamente
        // la contraseña dentro de los datos de sesión.
        unset($usuario['contrasena']);


        // Guarda los datos del usuario en la sesión.
        // $_SESSION permite mantener información del usuario
        // mientras permanece conectado.
        $_SESSION['usuario'] = $usuario;


        // Devuelve los datos del usuario autenticado.
        return $usuario;
    }


    // Cierra la sesión del usuario.
    public function logout(): void
    {
        // Elimina los datos del usuario guardados en la sesión.
        unset($_SESSION['usuario']);


        // Destruye la sesión actual.
        session_destroy();
    }


    // Obtiene los datos del usuario que actualmente tiene sesión iniciada.
    //
    // ?array significa:
    // puede devolver un array o null.
    public function usuarioActual(): ?array
    {
        // Comprueba si existe $_SESSION['usuario'].
        //
        // Si existe:
        // devuelve los datos del usuario.
        //
        // Si no existe:
        // devuelve null.
        return $_SESSION['usuario'] ?? null;
    }
}

