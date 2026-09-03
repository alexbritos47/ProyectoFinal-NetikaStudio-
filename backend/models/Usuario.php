<?php


class Usuario
{
    // Las propiedades: qué datos tiene un usuario.
    private int $id;
    private string $name;
    private string $email;
    private string $passwordHash;   // la contraseña, pero encriptada
    private string $role;
    private bool $active;

    /**
     * CONSTRUCTOR
     * Se ejecuta solo cuando hacemos:  new User(1, 'Ana', ...)
     * $this se refiere al objeto que se está creando.
     */
    public function __construct($id, $name, $email, $passwordHash, $role, $active)
    {
        $this->id           = $id;
        $this->name         = $name;
        $this->email        = $email;
        $this->passwordHash = $passwordHash;
        $this->role         = $role;
        $this->active       = $active;
    }

    // ------------------------------------------------------------------
    // GETTERS: dejan LEER los datos, pero no modificarlos.
    // ------------------------------------------------------------------

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function isActive()
    {
        return $this->active;
    }

    /**
     * Fijate que NO hay un getPasswordHash().
     * La contraseña entra al objeto y no sale nunca más.
     */

    // ------------------------------------------------------------------
    // MÉTODOS: lo que el usuario sabe hacer.
    // ------------------------------------------------------------------

    /**
     * ¿Es correcta esta contraseña?
     *
     * password_verify() agarra lo que escribió la persona, lo encripta
     * igual que el original y compara. Nunca comparamos con == , y
     * NUNCA se puede desencriptar un hash: va en un solo sentido.
     */
    public function checkPassword($plainPassword)
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    /**
     * Convierte el objeto en un arreglo, para poder mandarlo como JSON. */
    public function toArray()
    {
        return [
            'id'     => $this->id,
            'nombre' => $this->name,
            'email'  => $this->email,
            'rol'    => $this->role,
            'activo' => $this->active,
        ];
    }
}
