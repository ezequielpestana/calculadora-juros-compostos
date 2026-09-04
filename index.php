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


    <?php 
    
    $valorInicial = 20000;
    $valorMensal = 0;
    $taxaJurosMensal = 1;
    $periodoMeses = 12 * 8;
    $valorAtual = $valorInicial;
    $mesAtual = 1;
    $totalJuros = 0;

    ?>

    <h2 class="text-4xl font-bold">Calculadora Juros Compostos</h2>

    <table class="border border-collapse mt-4 text-center">
        <tr class="border">
            <td class="border p-1">Valor Mensal</td>
            <td class="border p-1">Mês</td>
            <td class="border p-1">Valor Total</td>
        </tr>

        <?php for($periodoMeses; $periodoMeses > 0; $periodoMeses--){     
            
                $valorAtual = $valorAtual * ($taxaJurosMensal / 100 + 1) + $valorMensal;
            
        ?>
        <tr class="border">
            <td class="border borde p-1"><?= $valorMensal ?></td></td>
            <td class="border borde p-1"><?php echo $mesAtual++; ?></td></td>
            <td class="border borde p-1"><?= number_format($valorAtual, 2)?></td></td>
        </tr>
        <?php }?>

    
       
        
    </table>
    </body>
</html>
