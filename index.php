<?php
$viteOrigin = 'http://127.0.0.1:5173';
$viteErrorCode = 0;
$viteErrorMessage = '';
$viteSocket = @fsockopen('127.0.0.1', 5173, $viteErrorCode, $viteErrorMessage, 0.05);
$viteIsRunning = is_resource($viteSocket);

if ($viteIsRunning) {
    fclose($viteSocket);
}
?>
<html>
    <head>
        <?php if ($viteIsRunning): ?>
            <script type="module" src="<?= $viteOrigin ?>/@vite/client"></script>
            <script type="module" src="<?= $viteOrigin ?>/src/main.js"></script>
        <?php else: ?>
            <link rel="stylesheet" href="dist/assets/style.css">
        <?php endif; ?>
    </head>
    <body>
    <h2 class="text-sm font-bold">Calculadora Juros Compostos aaskhdj</h2>
    <?php  
    
    echo('Olá'); 
    
    ?>
    <table style="border: 1;border-color: black;">
        <tr>
            <td>Valor Mensal</td>
            <td>Mês</td>
            olá
        </tr>
        <tr>
            <td>100000000</td>
</td>
        </tr>
    </table>
    </body>
</html>
