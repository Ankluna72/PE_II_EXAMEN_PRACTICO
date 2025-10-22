<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Estratégico de TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="css/style.css" rel="stylesheet">
    <?php
    if (isset($pageStyles) && is_array($pageStyles)) {
        foreach ($pageStyles as $cssFile) {
            echo '<link href="' . htmlspecialchars($cssFile) . '" rel="stylesheet">';
        }
    }
    ?>
</head>
<body>
    <div class="container mt-4">