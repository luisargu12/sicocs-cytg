<?php

class AnioModel
{
    public function iniciar()
    {
        if (($_SESSION['loggedin'] ?? false) !== true || (int) ($_SESSION['tipo'] ?? 0) !== 1) {
            header('Location: /');
            exit;
        }

        $conexion = Database::connection();
        $siguiente = self::siguiente($conexion);
        $solicitado = (int) ($_POST['year'] ?? 0);

        if ($solicitado !== $siguiente) {
            $this->regresar('Solo se puede iniciar el año siguiente.');
        }
        if (!self::habilitado($siguiente)) {
            $this->regresar('El año ' . $siguiente . ' se puede iniciar a partir del 1 de enero.');
        }

        $existe = $conexion->prepare('SELECT id_year FROM comites_year WHERE year = ?');
        $existe->execute([$siguiente]);
        if ($existe->fetch()) {
            $this->regresar('El año ' . $siguiente . ' ya está iniciado.');
        }

        $stmt = $conexion->prepare('INSERT INTO comites_year (year, ultimo_insert, ultimo_seguimiento) VALUES (?, 0, 0)');
        $stmt->execute([$siguiente]);

        header('Location: /iniciar-anio?ok=' . $siguiente);
        exit;
    }

    public static function siguiente(PDO $conexion)
    {
        $ultimo = (int) $conexion->query('SELECT MAX(year) FROM comites_year')->fetchColumn();
        if ($ultimo < 2000) {
            return (int) (new DateTime('now', new DateTimeZone('America/Monterrey')))->format('Y');
        }
        return $ultimo + 1;
    }

    public static function habilitado($year)
    {
        $zona = new DateTimeZone('America/Monterrey');
        $hoy = new DateTime('now', $zona);
        $apertura = new DateTime($year . '-01-01 00:00:00', $zona);
        return $hoy >= $apertura;
    }

    private function regresar($mensaje)
    {
        header('Location: /iniciar-anio?error=' . rawurlencode($mensaje));
        exit;
    }
}
