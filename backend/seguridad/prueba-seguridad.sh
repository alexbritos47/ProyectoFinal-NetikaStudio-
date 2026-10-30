
#!/usr/bin/env bash

# =====================================================
# AUDITORIA AUTOMATIZADA DE SEGURIDAD
# Proyecto: Club Ciclista Maragato
# Equipo: Netika Studio
# =====================================================

set -u

BASE_URL="${BASE_URL:-http://localhost:8080}"

PASS=0
FAIL=0
SKIP=0

# Archivos temporales para mantener las sesiones PHP.
SOCIO_COOKIE=$(mktemp)
trap 'rm -f "$SOCIO_COOKIE"' EXIT

# -----------------------------------------------------
# FUNCION: REALIZAR PETICIONES HTTP
# -----------------------------------------------------

request() {
    local method="$1"
    local url="$2"
    local cookie="${3:-}"
    local data="${4:-}"

    local args=(-sS -o /dev/null -w "%{http_code}"
                --max-time 10 -X "$method")

    if [[ -n "$cookie" ]]; then
        args+=(-b "$cookie" -c "$cookie")
    fi

    if [[ -n "$data" ]]; then
        args+=(-H "Content-Type: application/json" -d "$data")
    fi

    local result
    result=$(curl "${args[@]}" "$url" 2>/dev/null) || true

    echo "${result:-000}"
}

# -----------------------------------------------------
# FUNCION: REGISTRAR RESULTADOS
# -----------------------------------------------------

check() {
    local name="$1"
    local code="$2"
    shift 2

    for expected in "$@"; do
        if [[ "$code" == "$expected" ]]; then
            echo "[OK] $name - HTTP $code"
            ((PASS+=1))
            return
        fi
    done

    echo "[FALLO] $name - HTTP $code"
    ((FAIL+=1))
}

skip() {
    echo "[OMITIDA] $1 - Faltan datos para realizar la prueba"
    ((SKIP+=1))
}

# Escapa comillas y barras para construir JSON.
json_escape() {
    printf '%s' "$1" | sed 's/\\/\\\\/g; s/"/\\"/g'
}

# -----------------------------------------------------
# INICIO
# -----------------------------------------------------

echo "================================================"
echo " AUDITORIA DE SEGURIDAD - CLUB CICLISTA MARAGATO"
echo "================================================"
echo "API: $BASE_URL"
echo

# Comprobar que la API responde.
HTTP=$(request GET "$BASE_URL")

if [[ "$HTTP" == "000" ]]; then
    echo "[ERROR] No se pudo conectar con la API."
    echo "Verificá que Docker esté funcionando."
    exit 1
fi

echo "[INFO] API disponible - HTTP $HTTP"
echo

# -----------------------------------------------------
# 1. AUTENTICACION EN ENDPOINT PRIVADO
# -----------------------------------------------------

echo "1. AUTENTICACION EN ENDPOINTS PRIVADOS"

HTTP=$(request GET "$BASE_URL/api/auth/me")

check "Acceso a /api/auth/me sin iniciar sesion" "$HTTP" 401 403

echo

# -----------------------------------------------------
# 2. DATOS INVALIDOS EN FORMULARIOS
# -----------------------------------------------------

echo "2. VALIDACION DE DATOS INVALIDOS"

HTTP=$(request POST "$BASE_URL/api/auth/login" "" '{}')

check "Rechazo de inicio de sesion con campos vacios" "$HTTP" 400

echo

# -----------------------------------------------------
# 3. LIMITE DE INTENTOS DE INICIO DE SESION
# -----------------------------------------------------

echo "3. LIMITE DE INTENTOS DE INICIO DE SESION"

RATE_LIMITED=0

for i in {1..6}; do
    HTTP=$(request POST "$BASE_URL/api/auth/login" "" \
        '{"nombre_usuario":"auditoria_usuario_inexistente","contrasena":"clave_incorrecta"}')

    echo "Intento $i: HTTP $HTTP"

    if [[ "$HTTP" == "429" ]]; then
        RATE_LIMITED=1
        break
    fi
done

if [[ "$RATE_LIMITED" == "1" ]]; then
    check "Bloqueo por exceso de intentos" "429" 429
else
    check "Bloqueo por exceso de intentos" "NO_DETECTADO" 429
fi

echo

# -----------------------------------------------------
# 4. AUTENTICACION COMO SOCIO DE PRUEBA
# -----------------------------------------------------

echo "4. PREPARACION DE SESION DE SOCIO"

SOCIO_USERNAME="${SOCIO_USERNAME:-}"
SOCIO_PASSWORD="${SOCIO_PASSWORD:-}"

if [[ -z "$SOCIO_USERNAME" ]]; then
    read -r -p "Nombre de usuario del socio de prueba: " SOCIO_USERNAME
fi

if [[ -z "$SOCIO_PASSWORD" ]]; then
    read -r -s -p "Contrasena del socio de prueba: " SOCIO_PASSWORD
    echo
fi

USER_JSON=$(json_escape "$SOCIO_USERNAME")
PASS_JSON=$(json_escape "$SOCIO_PASSWORD")

LOGIN_JSON="{\"nombre_usuario\":\"$USER_JSON\",\"contrasena\":\"$PASS_JSON\"}"

HTTP=$(request POST "$BASE_URL/api/auth/login" "$SOCIO_COOKIE" "$LOGIN_JSON")

if [[ "$HTTP" == "200" ]]; then
    echo "[OK] Inicio de sesion como socio - HTTP $HTTP"
    ((PASS+=1))

    # -------------------------------------------------
    # 5. CONTROL DE ACCESO IDOR
    # -------------------------------------------------

    echo
    echo "5. CONTROL DE ACCESO IDOR"

    OTRO_SOCIO_ID="${OTRO_SOCIO_ID:-}"

    if [[ -z "$OTRO_SOCIO_ID" ]]; then
        read -r -p "ID de otro socio existente para la prueba: " OTRO_SOCIO_ID
    fi

    if [[ -n "$OTRO_SOCIO_ID" ]]; then
        HTTP=$(request GET "$BASE_URL/api/socios/$OTRO_SOCIO_ID" "$SOCIO_COOKIE")

        check "Socio intentando consultar datos de otro socio" "$HTTP" 403
    else
        skip "IDOR"
    fi

    # -------------------------------------------------
    # 6. ESCALADA DE PRIVILEGIOS
    # -------------------------------------------------

    echo
    echo "6. ESCALADA DE PRIVILEGIOS"

    HTTP=$(request GET "$BASE_URL/api/usuarios" "$SOCIO_COOKIE")

    check "Socio intentando acceder al listado de usuarios" "$HTTP" 403

else
    echo "[FALLO] No se pudo iniciar sesion como socio - HTTP $HTTP"
    ((FAIL+=1))
    skip "IDOR"
    skip "Escalada de privilegios"
fi

# -----------------------------------------------------
# RESULTADOS
# -----------------------------------------------------

echo
echo "================================================"
echo " RESULTADO DE LA AUDITORIA"
echo "================================================"
echo "Pruebas superadas: $PASS"
echo "Pruebas fallidas:  $FAIL"
echo "Pruebas omitidas:  $SKIP"
echo "================================================"

echo
echo "NOTA: Las pruebas deben interpretarse junto con"
echo "los codigos HTTP y los datos utilizados."
echo "Un resultado correcto no garantiza la ausencia"
echo "de otras vulnerabilidades."
