<?php

declare(strict_types=1);

$avisosValidacao = [];

function receberNumero(
    string $campo,
    string $rotulo,
    float $padrao,
    float $minimo,
    float $maximo,
    array &$avisos
): float
{
    if (! array_key_exists($campo, $_GET)) {
        return $padrao;
    }

    $valor = $_GET[$campo];

    if (! is_scalar($valor)) {
        $avisos[] = "O campo {$rotulo} recebeu um valor inválido e voltou ao padrão.";

        return $padrao;
    }

    $valorNormalizado = str_replace(',', '.', trim((string) $valor));

    if ($valorNormalizado === '' || ! is_numeric($valorNormalizado)) {
        $avisos[] = "O campo {$rotulo} precisa ser numérico e voltou ao padrão.";

        return $padrao;
    }

    $numero = (float) $valorNormalizado;

    if (! is_finite($numero) || $numero < $minimo || $numero > $maximo) {
        $avisos[] = "O campo {$rotulo} ficou fora do limite permitido e voltou ao padrão.";

        return $padrao;
    }

    return $numero;
}

function receberInteiro(
    string $campo,
    string $rotulo,
    int $padrao,
    int $minimo,
    int $maximo,
    array &$avisos
): int
{
    if (! array_key_exists($campo, $_GET)) {
        return $padrao;
    }

    $valor = $_GET[$campo];

    if (! is_scalar($valor)) {
        $avisos[] = "O campo {$rotulo} recebeu um valor inválido e voltou ao padrão.";

        return $padrao;
    }

    $numero = filter_var($valor, FILTER_VALIDATE_INT);

    if ($numero === false || $numero < $minimo || $numero > $maximo) {
        $avisos[] = "O campo {$rotulo} precisa ser um número inteiro dentro do limite e voltou ao padrão.";

        return $padrao;
    }

    return $numero;
}

function receberOpcao(
    string $campo,
    string $rotulo,
    string $padrao,
    array $opcoes,
    array &$avisos
): string
{
    if (! array_key_exists($campo, $_GET)) {
        return $padrao;
    }

    $valor = $_GET[$campo];

    if (! is_string($valor) || ! in_array($valor, $opcoes, true)) {
        $avisos[] = "O campo {$rotulo} recebeu uma opção inválida e voltou ao padrão.";

        return $padrao;
    }

    return $valor;
}

function calcularInvestimento(
    float $valorInicial,
    float $valorMensal,
    float $taxaMensal,
    int $periodoMeses
): array {
    $saldo = $valorInicial;
    $totalInvestido = $valorInicial;
    $parcelas = [];

    for ($mes = 1; $mes <= $periodoMeses; $mes++) {
        $jurosDoMes = $saldo * ($taxaMensal / 100);
        $saldo += $jurosDoMes + $valorMensal;
        $totalInvestido += $valorMensal;

        $parcelas[] = [
            'mes' => $mes,
            'aporte' => $valorMensal,
            'juros' => $jurosDoMes,
            'juros_acumulados' => $saldo - $totalInvestido,
            'saldo' => $saldo,
        ];
    }

    return [
        'total' => $saldo,
        'total_investido' => $totalInvestido,
        'total_juros' => $saldo - $totalInvestido,
        'parcelas' => $parcelas,
    ];
}

function calcularFinanciamentoPrice(float $principal, float $taxaMensal, int $numeroParcelas): array
{
    $taxaDecimal = $taxaMensal / 100;
    $denominador = $taxaDecimal === 0.0
        ? 0.0
        : -expm1(-$numeroParcelas * log1p($taxaDecimal));
    $prestacaoFixa = round(
        $taxaDecimal === 0.0 || $denominador === 0.0
            ? $principal / $numeroParcelas
            : $principal * ($taxaDecimal / $denominador),
        2
    );

    $saldo = round($principal, 2);
    $totalPago = 0.0;
    $totalJuros = 0.0;
    $parcelas = [];

    for ($mes = 1; $mes <= $numeroParcelas; $mes++) {
        $juros = round($saldo * $taxaDecimal, 2);
        $amortizacao = round($prestacaoFixa - $juros, 2);
        $prestacao = $prestacaoFixa;

        if ($mes === $numeroParcelas) {
            $amortizacao = $saldo;
            $prestacao = round($amortizacao + $juros, 2);
        }

        $saldo = round(max(0.0, $saldo - $amortizacao), 2);
        $totalPago = round($totalPago + $prestacao, 2);
        $totalJuros = round($totalJuros + $juros, 2);

        $parcelas[] = [
            'mes' => $mes,
            'prestacao' => $prestacao,
            'juros' => $juros,
            'amortizacao' => $amortizacao,
            'saldo' => $saldo,
        ];
    }

    return [
        'total' => $totalPago,
        'total_juros' => $totalJuros,
        'parcela_inicial' => $parcelas[0]['prestacao'],
        'parcela_final' => $parcelas[array_key_last($parcelas)]['prestacao'],
        'parcelas' => $parcelas,
    ];
}

function calcularFinanciamentoSac(float $principal, float $taxaMensal, int $numeroParcelas): array
{
    $taxaDecimal = $taxaMensal / 100;
    $amortizacaoConstante = round($principal / $numeroParcelas, 2);
    $saldo = round($principal, 2);
    $totalPago = 0.0;
    $totalJuros = 0.0;
    $parcelas = [];

    for ($mes = 1; $mes <= $numeroParcelas; $mes++) {
        $juros = round($saldo * $taxaDecimal, 2);
        $amortizacao = $mes === $numeroParcelas ? $saldo : $amortizacaoConstante;
        $prestacao = round($amortizacao + $juros, 2);
        $saldo = round(max(0.0, $saldo - $amortizacao), 2);
        $totalPago = round($totalPago + $prestacao, 2);
        $totalJuros = round($totalJuros + $juros, 2);

        $parcelas[] = [
            'mes' => $mes,
            'prestacao' => $prestacao,
            'juros' => $juros,
            'amortizacao' => $amortizacao,
            'saldo' => $saldo,
        ];
    }

    return [
        'total' => $totalPago,
        'total_juros' => $totalJuros,
        'parcela_inicial' => $parcelas[0]['prestacao'],
        'parcela_final' => $parcelas[array_key_last($parcelas)]['prestacao'],
        'parcelas' => $parcelas,
    ];
}

function calcularMontanteSemAmortizacao(float $principal, float $taxaMensal, int $periodoMeses): array
{
    $saldo = round($principal, 2);
    $parcelas = [];

    for ($mes = 1; $mes <= $periodoMeses; $mes++) {
        $juros = round($saldo * ($taxaMensal / 100), 2);
        $saldo = round($saldo + $juros, 2);

        $parcelas[] = [
            'mes' => $mes,
            'prestacao' => 0.0,
            'juros' => $juros,
            'amortizacao' => 0.0,
            'saldo' => $saldo,
        ];
    }

    return [
        'total' => $saldo,
        'total_juros' => $saldo - $principal,
        'parcela_inicial' => 0.0,
        'parcela_final' => 0.0,
        'parcelas' => $parcelas,
    ];
}

function calcularFinanciamento(
    string $sistema,
    float $principal,
    float $taxaMensal,
    int $numeroParcelas
): array {
    return match ($sistema) {
        'sac' => calcularFinanciamentoSac($principal, $taxaMensal, $numeroParcelas),
        'composto' => calcularMontanteSemAmortizacao($principal, $taxaMensal, $numeroParcelas),
        default => calcularFinanciamentoPrice($principal, $taxaMensal, $numeroParcelas),
    };
}

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarPercentual(float $valor): string
{
    return number_format($valor, 2, ',', '.') . '%';
}

function formatarNumeroInput(float $valor, int $casasDecimais = 2): string
{
    $formatado = number_format($valor, $casasDecimais, '.', '');

    return rtrim(rtrim($formatado, '0'), '.') ?: '0';
}

$valorInicial = receberNumero('valor_inicial', 'Valor inicial', 0, 0, 999999999.99, $avisosValidacao);
$valorMensal = receberNumero('valor_mensal', 'Aporte mensal', 200, 0, 999999999.99, $avisosValidacao);
$taxaJurosMensal = receberNumero('taxa_juros', 'Taxa mensal do investimento', 1, 0, 100, $avisosValidacao);
$periodoMeses = receberInteiro('periodo_meses', 'Prazo do investimento', 96, 1, 600, $avisosValidacao);
$resultadoInvestimento = calcularInvestimento(
    $valorInicial,
    $valorMensal,
    $taxaJurosMensal,
    $periodoMeses
);

$valorFinanciado = receberNumero('valor_financiado', 'Saldo devedor', 50000, 0.01, 999999999.99, $avisosValidacao);
$prazoFinanciamento = receberInteiro('prazo_financiamento', 'Prazo restante', 48, 1, 600, $avisosValidacao);
$taxaContratoAtual = receberNumero('taxa_contrato_atual', 'Taxa atual', 2, 0, 100, $avisosValidacao);
$taxaNovaProposta = receberNumero('taxa_nova_proposta', 'Nova taxa', 1.8, 0, 100, $avisosValidacao);
$sistemaAmortizacao = receberOpcao(
    'sistema_amortizacao',
    'Sistema de cálculo',
    'price',
    ['price', 'sac', 'composto'],
    $avisosValidacao
);
$resultadoContratoAtual = calcularFinanciamento(
    $sistemaAmortizacao,
    $valorFinanciado,
    $taxaContratoAtual,
    $prazoFinanciamento
);
$resultadoNovaProposta = calcularFinanciamento(
    $sistemaAmortizacao,
    $valorFinanciado,
    $taxaNovaProposta,
    $prazoFinanciamento
);

$totalContratoAtual = round($resultadoContratoAtual['total'], 2);
$totalNovaProposta = round($resultadoNovaProposta['total'], 2);
$resultadosIguais = $totalContratoAtual === $totalNovaProposta;
$contratoAtualMaisBarato = $totalContratoAtual < $totalNovaProposta;
$economiaFinanciamento = abs($totalContratoAtual - $totalNovaProposta);
$simulacaoSemAmortizacao = $sistemaAmortizacao === 'composto';
$ultimaParcelaAtualAjustada = abs(
    $resultadoContratoAtual['parcela_inicial'] - $resultadoContratoAtual['parcela_final']
) >= 0.005;
$ultimaParcelaNovaAjustada = abs(
    $resultadoNovaProposta['parcela_inicial'] - $resultadoNovaProposta['parcela_final']
) >= 0.005;
$nomeSistema = match ($sistemaAmortizacao) {
    'sac' => 'SAC · parcelas decrescentes',
    'composto' => 'Montante composto · sem parcelas',
    default => 'Price · parcelas fixas',
};

$viteOrigin = 'http://127.0.0.1:5173';
$viteErrorCode = 0;
$viteErrorMessage = '';
$viteSocket = @fsockopen('127.0.0.1', 5173, $viteErrorCode, $viteErrorMessage, 0.05);
$viteIsRunning = is_resource($viteSocket);

if ($viteIsRunning) {
    fclose($viteSocket);
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="color-scheme" content="dark">
        <meta name="description" content="Simule juros compostos e compare o valor nominal de financiamentos pelos sistemas Price e SAC.">
        <title>Calculadora e Comparador de Juros</title>

        <?php if ($viteIsRunning): ?>
            <script type="module" src="<?= $viteOrigin ?>/@vite/client"></script>
            <script type="module" src="<?= $viteOrigin ?>/src/main.js"></script>
        <?php else: ?>
            <link rel="stylesheet" href="dist/assets/style.css">
        <?php endif; ?>
    </head>

    <body class="min-w-[250px] overflow-x-hidden bg-slate-950 font-sans text-slate-100 antialiased selection:bg-emerald-300 selection:text-slate-950">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <div class="absolute -left-36 -top-32 size-80 rounded-full bg-emerald-400/10 blur-3xl sm:size-[28rem]"></div>
            <div class="absolute -right-44 top-72 size-96 rounded-full bg-cyan-400/10 blur-3xl sm:size-[32rem]"></div>
        </div>

        <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-2 py-4 min-[320px]:px-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <a href="." class="flex min-w-0 items-center gap-3" aria-label="Reiniciar calculadora">
                    <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-emerald-300 to-cyan-300 text-slate-950 shadow-lg shadow-emerald-400/15">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 19V9m5 10V5m5 14v-7m5 7V3" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold tracking-wide text-white">Juros em foco</span>
                        <span class="block truncate text-xs text-slate-500">Simule antes de decidir</span>
                    </span>
                </a>

                <nav class="flex flex-wrap gap-2 text-xs font-semibold min-[360px]:text-sm" aria-label="Navegação principal">
                    <a href="#juros-compostos" class="rounded-full border border-white/10 px-3 py-2 text-slate-300 transition hover:border-emerald-300/50 hover:text-white">Investimento</a>
                    <a href="#comparar-financiamentos" class="rounded-full bg-white/10 px-3 py-2 text-white transition hover:bg-white/15">Comparar crédito</a>
                </nav>
            </div>
        </header>

        <main>
            <section class="mx-auto grid w-full max-w-7xl gap-8 px-2 pb-10 pt-10 min-[320px]:px-3 sm:px-6 sm:pb-14 sm:pt-16 lg:grid-cols-[minmax(0,1.35fr)_minmax(17rem,0.65fr)] lg:items-end lg:px-8 lg:pb-20 lg:pt-24">
                <div>
                    <div class="mb-5 inline-flex max-w-full items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-emerald-200 min-[360px]:text-xs">
                        <span class="size-1.5 shrink-0 rounded-full bg-emerald-300 shadow-[0_0_12px_rgba(110,231,183,0.9)]"></span>
                        Decisões financeiras mais claras
                    </div>
                    <h1 class="max-w-4xl text-[1.85rem] font-black leading-[1.06] tracking-[-0.045em] text-white min-[320px]:text-4xl sm:text-5xl lg:text-6xl">
                        Entenda o custo real do
                        <span class="bg-gradient-to-r from-emerald-300 via-cyan-300 to-sky-400 bg-clip-text text-transparent">tempo sobre o dinheiro.</span>
                    </h1>
                    <p class="mt-5 max-w-2xl text-sm leading-6 text-slate-400 min-[360px]:text-base sm:text-lg sm:leading-8">
                        Projete investimentos e compare o total nominal de um contrato atual com uma nova proposta de financiamento.
                    </p>
                </div>

                <div class="min-w-0 rounded-3xl border border-white/10 bg-white/[0.055] p-3 shadow-2xl shadow-black/25 backdrop-blur min-[360px]:p-4 sm:p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500 min-[360px]:text-xs">Economia na comparação atual</p>
                    <p class="mt-3 break-words text-2xl font-black tracking-tight text-emerald-300 min-[360px]:text-3xl"><?= formatarMoeda($economiaFinanciamento) ?></p>
                    <div class="mt-4 flex flex-col gap-2 border-t border-white/10 pt-4 text-xs min-[400px]:flex-row min-[400px]:items-center min-[400px]:justify-between sm:text-sm lg:flex-col lg:items-start xl:flex-row xl:items-center">
                        <span class="text-slate-500"><?= $prazoFinanciamento ?> meses</span>
                        <span class="w-fit rounded-full bg-cyan-300/10 px-2.5 py-1 font-semibold text-cyan-200"><?= $nomeSistema ?></span>
                    </div>
                </div>
            </section>

            <form method="get" action="" class="mx-auto w-full max-w-7xl space-y-10 px-2 pb-14 min-[320px]:px-3 sm:space-y-14 sm:px-6 sm:pb-20 lg:px-8">
                <?php if ($avisosValidacao !== []): ?>
                    <div role="alert" class="rounded-2xl border border-amber-300/20 bg-amber-300/[0.07] p-3 text-sm text-amber-100 min-[360px]:p-4">
                        <p class="font-bold">Alguns valores não puderam ser usados:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5 text-slate-300">
                            <?php foreach ($avisosValidacao as $aviso): ?>
                                <li><?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <section id="juros-compostos" class="scroll-mt-6 rounded-[1.5rem] border border-white/10 bg-slate-900/75 shadow-2xl shadow-black/20 sm:rounded-[2rem]">
                    <div class="border-b border-white/10 p-3 min-[360px]:p-4 sm:p-6 lg:p-8">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-300">01 · Acumulação</span>
                                <h2 class="mt-2 text-xl font-bold tracking-tight text-white min-[360px]:text-2xl sm:text-3xl">Calculadora de juros compostos</h2>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">Mantém a lógica original: primeiro rende o saldo anterior e depois adiciona o aporte do mês.</p>
                            </div>
                            <a href="." class="w-fit text-xs font-semibold text-slate-500 underline decoration-slate-700 underline-offset-4 transition hover:text-white">Restaurar valores</a>
                        </div>
                    </div>

                    <div class="grid min-w-0 xl:grid-cols-[minmax(18rem,0.72fr)_minmax(0,1.28fr)]">
                        <fieldset class="min-w-0 border-b border-white/10 p-3 min-[360px]:p-4 sm:p-6 xl:border-b-0 xl:border-r lg:p-8">
                            <legend class="sr-only">Dados do investimento</legend>
                            <div class="grid min-w-0 gap-4 min-[520px]:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                                <label class="block min-w-0">
                                    <span class="mb-2 block text-sm font-medium text-slate-300">Valor inicial</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-white/10 bg-slate-950/70 transition focus-within:border-emerald-300/70 focus-within:ring-4 focus-within:ring-emerald-300/10">
                                        <span class="shrink-0 pl-3 text-sm text-slate-500">R$</span>
                                        <input type="number" name="valor_inicial" value="<?= formatarNumeroInput($valorInicial) ?>" min="0" max="999999999.99" step="0.01" inputmode="decimal" class="min-w-0 flex-1 bg-transparent px-2 py-3 text-base font-semibold text-white outline-none" required>
                                    </span>
                                </label>

                                <label class="block min-w-0">
                                    <span class="mb-2 block text-sm font-medium text-slate-300">Aporte mensal</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-white/10 bg-slate-950/70 transition focus-within:border-emerald-300/70 focus-within:ring-4 focus-within:ring-emerald-300/10">
                                        <span class="shrink-0 pl-3 text-sm text-slate-500">R$</span>
                                        <input type="number" name="valor_mensal" value="<?= formatarNumeroInput($valorMensal) ?>" min="0" max="999999999.99" step="0.01" inputmode="decimal" class="min-w-0 flex-1 bg-transparent px-2 py-3 text-base font-semibold text-white outline-none" required>
                                    </span>
                                </label>

                                <label class="block min-w-0">
                                    <span class="mb-2 block text-sm font-medium text-slate-300">Taxa mensal</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-white/10 bg-slate-950/70 transition focus-within:border-emerald-300/70 focus-within:ring-4 focus-within:ring-emerald-300/10">
                                        <input type="number" name="taxa_juros" value="<?= formatarNumeroInput($taxaJurosMensal, 4) ?>" min="0" max="100" step="0.0001" inputmode="decimal" class="min-w-0 flex-1 bg-transparent px-3 py-3 text-base font-semibold text-white outline-none" required>
                                        <span class="shrink-0 pr-3 text-xs text-slate-500 min-[320px]:text-sm">% a.m.</span>
                                    </span>
                                </label>

                                <label class="block min-w-0">
                                    <span class="mb-2 block text-sm font-medium text-slate-300">Prazo</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-white/10 bg-slate-950/70 transition focus-within:border-emerald-300/70 focus-within:ring-4 focus-within:ring-emerald-300/10">
                                        <input type="number" name="periodo_meses" value="<?= $periodoMeses ?>" min="1" max="600" step="1" inputmode="numeric" class="min-w-0 flex-1 bg-transparent px-3 py-3 text-base font-semibold text-white outline-none" required>
                                        <span class="shrink-0 pr-3 text-xs text-slate-500 min-[320px]:text-sm">meses</span>
                                    </span>
                                </label>
                            </div>

                            <button type="submit" formaction="#juros-compostos" class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-300 to-cyan-300 px-3 py-3 text-sm font-black text-slate-950 shadow-lg shadow-emerald-400/10 transition hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-emerald-300/25 active:scale-[0.99]">
                                Atualizar projeção
                                <svg viewBox="0 0 24 24" class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                        </fieldset>

                        <div class="min-w-0 p-3 min-[360px]:p-4 sm:p-6 lg:p-8">
                            <div class="grid min-w-0 gap-3 min-[480px]:grid-cols-3">
                                <article class="min-w-0 rounded-2xl border border-emerald-300/20 bg-emerald-300/[0.07] p-3 min-[360px]:p-4">
                                    <p class="text-xs font-medium text-emerald-200/70">Saldo projetado</p>
                                    <p class="mt-2 break-words text-lg font-black tabular-nums text-white min-[360px]:text-xl"><?= formatarMoeda($resultadoInvestimento['total']) ?></p>
                                </article>
                                <article class="min-w-0 rounded-2xl border border-white/10 bg-white/[0.035] p-3 min-[360px]:p-4">
                                    <p class="text-xs font-medium text-slate-500">Total investido</p>
                                    <p class="mt-2 break-words text-lg font-black tabular-nums text-white min-[360px]:text-xl"><?= formatarMoeda($resultadoInvestimento['total_investido']) ?></p>
                                </article>
                                <article class="min-w-0 rounded-2xl border border-white/10 bg-white/[0.035] p-3 min-[360px]:p-4">
                                    <p class="text-xs font-medium text-slate-500">Juros acumulados</p>
                                    <p class="mt-2 break-words text-lg font-black tabular-nums text-cyan-300 min-[360px]:text-xl"><?= formatarMoeda($resultadoInvestimento['total_juros']) ?></p>
                                </article>
                            </div>

                            <details class="group mt-5 overflow-hidden rounded-2xl border border-white/10 bg-slate-950/35">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-3 text-sm font-bold text-white marker:content-none min-[360px]:p-4 [&::-webkit-details-marker]:hidden">
                                    <span class="min-w-0">Evolução mês a mês <span class="mt-0.5 block text-xs font-normal text-slate-500"><?= $periodoMeses ?> registros</span></span>
                                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/5 text-slate-400 transition group-open:rotate-180"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                                </summary>

                                <div class="max-h-[34rem] overflow-y-auto border-t border-white/10 md:hidden">
                                    <?php foreach ($resultadoInvestimento['parcelas'] as $parcela): ?>
                                        <article class="grid grid-cols-2 gap-3 border-b border-white/[0.07] p-3 text-xs last:border-b-0 min-[360px]:p-4">
                                            <div><p class="text-slate-500">Mês</p><p class="mt-1 font-bold text-white"><?= $parcela['mes'] ?></p></div>
                                            <div class="text-right"><p class="text-slate-500">Saldo</p><p class="mt-1 break-words font-bold tabular-nums text-emerald-300"><?= formatarMoeda($parcela['saldo']) ?></p></div>
                                            <div><p class="text-slate-500">Aporte</p><p class="mt-1 break-words tabular-nums text-slate-300"><?= formatarMoeda($parcela['aporte']) ?></p></div>
                                            <div class="text-right"><p class="text-slate-500">Juros do mês</p><p class="mt-1 break-words tabular-nums text-cyan-300"><?= formatarMoeda($parcela['juros']) ?></p></div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>

                                <div class="hidden max-h-[34rem] overflow-auto md:block">
                                    <table class="w-full min-w-[42rem] border-collapse text-left text-sm">
                                        <thead class="sticky top-0 z-10 bg-slate-900 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Mês</th><th class="px-4 py-3 text-right">Aporte</th><th class="px-4 py-3 text-right">Juros do mês</th><th class="px-4 py-3 text-right">Juros acumulados</th><th class="px-4 py-3 text-right">Saldo</th></tr></thead>
                                        <tbody class="divide-y divide-white/[0.07]">
                                            <?php foreach ($resultadoInvestimento['parcelas'] as $parcela): ?>
                                                <tr class="transition hover:bg-white/[0.035]"><td class="px-4 py-3 font-bold text-white"><?= $parcela['mes'] ?></td><td class="px-4 py-3 text-right tabular-nums text-slate-300"><?= formatarMoeda($parcela['aporte']) ?></td><td class="px-4 py-3 text-right tabular-nums text-cyan-300"><?= formatarMoeda($parcela['juros']) ?></td><td class="px-4 py-3 text-right tabular-nums text-slate-300"><?= formatarMoeda($parcela['juros_acumulados']) ?></td><td class="px-4 py-3 text-right font-bold tabular-nums text-emerald-300"><?= formatarMoeda($parcela['saldo']) ?></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>
                    </div>
                </section>

                <section id="comparar-financiamentos" class="scroll-mt-6 overflow-hidden rounded-[1.5rem] border border-cyan-300/15 bg-gradient-to-br from-slate-900 via-slate-900 to-cyan-950/45 shadow-2xl shadow-black/20 sm:rounded-[2rem]">
                    <div class="border-b border-white/10 p-3 min-[360px]:p-4 sm:p-6 lg:p-8">
                        <span class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-300">02 · Portabilidade</span>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-white min-[360px]:text-2xl sm:text-3xl">Compare o total nominal dos contratos</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">Informe o saldo devedor atual e o prazo restante. O comparador soma as parcelas para mostrar quanto sai do seu bolso em cada taxa.</p>
                    </div>

                    <div class="grid min-w-0 xl:grid-cols-[minmax(18rem,0.72fr)_minmax(0,1.28fr)]">
                        <fieldset class="min-w-0 border-b border-white/10 p-3 min-[360px]:p-4 sm:p-6 xl:border-b-0 xl:border-r lg:p-8">
                            <legend class="sr-only">Dados para comparar os financiamentos</legend>
                            <div class="grid min-w-0 gap-4 min-[520px]:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                                <label class="block min-w-0 min-[520px]:col-span-2 xl:col-span-1 2xl:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-slate-300">Saldo devedor atual</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-white/10 bg-slate-950/70 transition focus-within:border-cyan-300/70 focus-within:ring-4 focus-within:ring-cyan-300/10">
                                        <span class="shrink-0 pl-3 text-sm text-slate-500">R$</span>
                                        <input type="number" name="valor_financiado" value="<?= formatarNumeroInput($valorFinanciado) ?>" min="0.01" max="999999999.99" step="0.01" inputmode="decimal" class="min-w-0 flex-1 bg-transparent px-2 py-3 text-base font-semibold text-white outline-none" required>
                                    </span>
                                </label>

                                <label class="block min-w-0 min-[520px]:col-span-2 xl:col-span-1 2xl:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-slate-300">Sistema de cálculo</span>
                                    <select name="sistema_amortizacao" class="min-h-12 w-full min-w-0 rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-base font-semibold text-white outline-none transition focus:border-cyan-300/70 focus:ring-4 focus:ring-cyan-300/10">
                                        <option value="price" <?= $sistemaAmortizacao === 'price' ? 'selected' : '' ?>>Price · parcelas fixas</option>
                                        <option value="sac" <?= $sistemaAmortizacao === 'sac' ? 'selected' : '' ?>>SAC · parcelas decrescentes</option>
                                        <option value="composto" <?= $sistemaAmortizacao === 'composto' ? 'selected' : '' ?>>Composto · sem amortização</option>
                                    </select>
                                </label>

                                <label class="block min-w-0 min-[520px]:col-span-2 xl:col-span-1 2xl:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-slate-300">Prazo restante</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-white/10 bg-slate-950/70 transition focus-within:border-cyan-300/70 focus-within:ring-4 focus-within:ring-cyan-300/10">
                                        <input type="number" name="prazo_financiamento" value="<?= $prazoFinanciamento ?>" min="1" max="600" step="1" inputmode="numeric" class="min-w-0 flex-1 bg-transparent px-3 py-3 text-base font-semibold text-white outline-none" required>
                                        <span class="shrink-0 pr-3 text-xs text-slate-500 min-[320px]:text-sm">meses</span>
                                    </span>
                                </label>

                                <label class="block min-w-0">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-300"><span class="size-2 rounded-full bg-amber-300"></span>Taxa atual</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-amber-300/20 bg-slate-950/70 transition focus-within:border-amber-300/70 focus-within:ring-4 focus-within:ring-amber-300/10">
                                        <input type="number" name="taxa_contrato_atual" value="<?= formatarNumeroInput($taxaContratoAtual, 4) ?>" min="0" max="100" step="0.0001" inputmode="decimal" class="min-w-0 flex-1 bg-transparent px-3 py-3 text-base font-semibold text-white outline-none" required>
                                        <span class="shrink-0 pr-2 text-[10px] text-slate-500 min-[320px]:pr-3 min-[320px]:text-xs">% a.m.</span>
                                    </span>
                                </label>

                                <label class="block min-w-0">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-300"><span class="size-2 rounded-full bg-sky-300"></span>Nova taxa</span>
                                    <span class="flex min-w-0 items-center rounded-xl border border-sky-300/20 bg-slate-950/70 transition focus-within:border-sky-300/70 focus-within:ring-4 focus-within:ring-sky-300/10">
                                        <input type="number" name="taxa_nova_proposta" value="<?= formatarNumeroInput($taxaNovaProposta, 4) ?>" min="0" max="100" step="0.0001" inputmode="decimal" class="min-w-0 flex-1 bg-transparent px-3 py-3 text-base font-semibold text-white outline-none" required>
                                        <span class="shrink-0 pr-2 text-[10px] text-slate-500 min-[320px]:pr-3 min-[320px]:text-xs">% a.m.</span>
                                    </span>
                                </label>
                            </div>

                            <button type="submit" formaction="#comparar-financiamentos" class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-300 to-sky-300 px-3 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-cyan-300/25 active:scale-[0.99]">
                                Comparar propostas
                                <svg viewBox="0 0 24 24" class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 7h11l-3-3m3 13H7l3 3" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>

                            <p class="mt-4 text-xs leading-5 text-slate-500">Use o saldo devedor fornecido pelo banco, não o valor originalmente contratado.</p>
                        </fieldset>

                        <div class="min-w-0 p-3 min-[360px]:p-4 sm:p-6 lg:p-8">
                            <div class="mb-4 flex flex-col gap-2 rounded-xl border border-white/10 bg-slate-950/45 p-3 text-xs min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                                <span class="text-slate-500">Método selecionado</span>
                                <span class="w-fit font-bold text-cyan-200"><?= $nomeSistema ?></span>
                            </div>

                            <div class="grid min-w-0 gap-4 lg:grid-cols-2">
                                <article class="min-w-0 rounded-2xl border border-amber-300/20 bg-amber-300/[0.045] p-3 min-[360px]:p-4 sm:p-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div><p class="flex items-center gap-2 text-sm font-bold text-white"><span class="size-2 rounded-full bg-amber-300"></span>Contrato atual</p><p class="mt-2 text-2xl font-black tracking-tight text-amber-200"><?= formatarPercentual($taxaContratoAtual) ?> <span class="text-xs font-medium text-slate-500">a.m.</span></p></div>
                                        <?php if (! $resultadosIguais && $contratoAtualMaisBarato): ?><span class="rounded-full bg-emerald-300 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-950">Menor custo</span><?php endif; ?>
                                    </div>
                                    <dl class="mt-5 space-y-3 border-t border-white/10 pt-4 text-sm">
                                        <div class="flex flex-col gap-1 min-[360px]:flex-row min-[360px]:items-center min-[360px]:justify-between"><dt class="text-slate-500"><?= $simulacaoSemAmortizacao ? 'Montante final' : 'Total nominal pago' ?></dt><dd class="break-words font-black tabular-nums text-white"><?= formatarMoeda($resultadoContratoAtual['total']) ?></dd></div>
                                        <div class="flex flex-col gap-1 min-[360px]:flex-row min-[360px]:items-center min-[360px]:justify-between"><dt class="text-slate-500">Total de juros</dt><dd class="break-words font-bold tabular-nums text-amber-200"><?= formatarMoeda($resultadoContratoAtual['total_juros']) ?></dd></div>
                                        <?php if (! $simulacaoSemAmortizacao): ?><div class="flex flex-col gap-1 min-[360px]:flex-row min-[360px]:items-center min-[360px]:justify-between"><dt class="text-slate-500"><?= $sistemaAmortizacao === 'price' ? 'Parcela / última' : 'Primeira → última' ?></dt><dd class="break-words text-right font-bold tabular-nums text-slate-200"><?= formatarMoeda($resultadoContratoAtual['parcela_inicial']) ?><?= $sistemaAmortizacao === 'sac' || $ultimaParcelaAtualAjustada ? ' → ' . formatarMoeda($resultadoContratoAtual['parcela_final']) : '' ?></dd></div><?php endif; ?>
                                    </dl>
                                </article>

                                <article class="min-w-0 rounded-2xl border border-sky-300/20 bg-sky-300/[0.045] p-3 min-[360px]:p-4 sm:p-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div><p class="flex items-center gap-2 text-sm font-bold text-white"><span class="size-2 rounded-full bg-sky-300"></span>Nova proposta</p><p class="mt-2 text-2xl font-black tracking-tight text-sky-200"><?= formatarPercentual($taxaNovaProposta) ?> <span class="text-xs font-medium text-slate-500">a.m.</span></p></div>
                                        <?php if (! $resultadosIguais && ! $contratoAtualMaisBarato): ?><span class="rounded-full bg-emerald-300 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-950">Menor custo</span><?php endif; ?>
                                    </div>
                                    <dl class="mt-5 space-y-3 border-t border-white/10 pt-4 text-sm">
                                        <div class="flex flex-col gap-1 min-[360px]:flex-row min-[360px]:items-center min-[360px]:justify-between"><dt class="text-slate-500"><?= $simulacaoSemAmortizacao ? 'Montante final' : 'Total nominal pago' ?></dt><dd class="break-words font-black tabular-nums text-white"><?= formatarMoeda($resultadoNovaProposta['total']) ?></dd></div>
                                        <div class="flex flex-col gap-1 min-[360px]:flex-row min-[360px]:items-center min-[360px]:justify-between"><dt class="text-slate-500">Total de juros</dt><dd class="break-words font-bold tabular-nums text-sky-200"><?= formatarMoeda($resultadoNovaProposta['total_juros']) ?></dd></div>
                                        <?php if (! $simulacaoSemAmortizacao): ?><div class="flex flex-col gap-1 min-[360px]:flex-row min-[360px]:items-center min-[360px]:justify-between"><dt class="text-slate-500"><?= $sistemaAmortizacao === 'price' ? 'Parcela / última' : 'Primeira → última' ?></dt><dd class="break-words text-right font-bold tabular-nums text-slate-200"><?= formatarMoeda($resultadoNovaProposta['parcela_inicial']) ?><?= $sistemaAmortizacao === 'sac' || $ultimaParcelaNovaAjustada ? ' → ' . formatarMoeda($resultadoNovaProposta['parcela_final']) : '' ?></dd></div><?php endif; ?>
                                    </dl>
                                </article>
                            </div>

                            <div class="mt-4 rounded-2xl border border-emerald-300/15 bg-emerald-300/[0.055] p-3 min-[360px]:p-4 sm:p-5">
                                <?php if ($resultadosIguais): ?>
                                    <p class="text-sm font-bold text-white">As duas opções têm o mesmo custo calculado.</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Informe taxas diferentes para visualizar a economia.</p>
                                <?php else: ?>
                                    <div class="flex flex-col gap-3 min-[440px]:flex-row min-[440px]:items-center min-[440px]:justify-between">
                                        <div><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 min-[360px]:text-xs">Economia nominal estimada</p><p class="mt-1 break-words text-xl font-black tabular-nums text-emerald-300 min-[360px]:text-2xl"><?= formatarMoeda($economiaFinanciamento) ?></p></div>
                                        <p class="max-w-xs text-xs leading-5 text-slate-400 min-[440px]:text-right"><?= $contratoAtualMaisBarato ? 'O contrato atual' : 'A nova proposta' ?> apresenta o menor total em <?= $prazoFinanciamento ?> meses.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <details class="group mt-4 overflow-hidden rounded-2xl border border-white/10 bg-slate-950/35">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-3 text-sm font-bold text-white marker:content-none min-[360px]:p-4 [&::-webkit-details-marker]:hidden">
                                    <span class="min-w-0">Comparação mês a mês <span class="mt-0.5 block text-xs font-normal text-slate-500"><?= $prazoFinanciamento ?> registros por opção</span></span>
                                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/5 text-slate-400 transition group-open:rotate-180"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                                </summary>

                                <div class="max-h-[34rem] overflow-y-auto border-t border-white/10 md:hidden">
                                    <?php foreach ($resultadoContratoAtual['parcelas'] as $indice => $parcelaAtual): ?>
                                        <?php $parcelaNova = $resultadoNovaProposta['parcelas'][$indice]; ?>
                                        <article class="border-b border-white/[0.07] p-3 text-xs last:border-b-0 min-[360px]:p-4">
                                            <p class="font-bold text-white">Mês <?= $parcelaAtual['mes'] ?></p>
                                            <div class="mt-3 grid grid-cols-2 gap-3">
                                                <div><p class="flex items-center gap-1.5 text-slate-500"><span class="size-1.5 rounded-full bg-amber-300"></span>Atual</p><p class="mt-1 break-words font-bold tabular-nums text-amber-200"><?= $simulacaoSemAmortizacao ? formatarMoeda($parcelaAtual['saldo']) : formatarMoeda($parcelaAtual['prestacao']) ?></p><p class="mt-1 break-words tabular-nums text-slate-500">Saldo: <?= formatarMoeda($parcelaAtual['saldo']) ?></p></div>
                                                <div class="text-right"><p class="flex items-center justify-end gap-1.5 text-slate-500"><span class="size-1.5 rounded-full bg-sky-300"></span>Nova</p><p class="mt-1 break-words font-bold tabular-nums text-sky-200"><?= $simulacaoSemAmortizacao ? formatarMoeda($parcelaNova['saldo']) : formatarMoeda($parcelaNova['prestacao']) ?></p><p class="mt-1 break-words tabular-nums text-slate-500">Saldo: <?= formatarMoeda($parcelaNova['saldo']) ?></p></div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>

                                <div class="hidden max-h-[34rem] overflow-auto md:block">
                                    <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
                                        <thead class="sticky top-0 z-10 bg-slate-900 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Mês</th><th class="px-4 py-3 text-right"><?= $simulacaoSemAmortizacao ? 'Montante atual' : 'Parcela atual' ?></th><th class="px-4 py-3 text-right">Saldo atual</th><th class="px-4 py-3 text-right"><?= $simulacaoSemAmortizacao ? 'Montante novo' : 'Parcela nova' ?></th><th class="px-4 py-3 text-right">Saldo novo</th></tr></thead>
                                        <tbody class="divide-y divide-white/[0.07]">
                                            <?php foreach ($resultadoContratoAtual['parcelas'] as $indice => $parcelaAtual): ?>
                                                <?php $parcelaNova = $resultadoNovaProposta['parcelas'][$indice]; ?>
                                                <tr class="transition hover:bg-white/[0.035]"><td class="px-4 py-3 font-bold text-white"><?= $parcelaAtual['mes'] ?></td><td class="px-4 py-3 text-right font-bold tabular-nums text-amber-200"><?= formatarMoeda($simulacaoSemAmortizacao ? $parcelaAtual['saldo'] : $parcelaAtual['prestacao']) ?></td><td class="px-4 py-3 text-right tabular-nums text-slate-400"><?= formatarMoeda($parcelaAtual['saldo']) ?></td><td class="px-4 py-3 text-right font-bold tabular-nums text-sky-200"><?= formatarMoeda($simulacaoSemAmortizacao ? $parcelaNova['saldo'] : $parcelaNova['prestacao']) ?></td><td class="px-4 py-3 text-right tabular-nums text-slate-400"><?= formatarMoeda($parcelaNova['saldo']) ?></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </details>

                            <div class="mt-4 flex items-start gap-3 rounded-xl border border-amber-300/10 bg-amber-300/[0.045] p-3 text-xs leading-5 text-slate-400">
                                <svg viewBox="0 0 24 24" class="mt-0.5 size-4 shrink-0 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" stroke-linecap="round" /></svg>
                                <p>
                                    <?php if ($simulacaoSemAmortizacao): ?>Este cenário apenas capitaliza o saldo, sem parcelas ou amortização. Ele não representa um financiamento bancário comum.<?php else: ?>A primeira parcela vence após um mês. Estimativa sem entrada, carência, IOF, seguros, tarifas ou indexadores. Para decidir uma portabilidade, compare também o CET informado pelas instituições.<?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <footer class="border-t border-white/10">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-2 py-6 text-xs text-slate-600 min-[320px]:px-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>Simulação educativa. Confirme os valores com a instituição financeira.</p>
                <a href="#" class="w-fit transition hover:text-slate-300">Voltar ao topo ↑</a>
            </div>
        </footer>
    </body>
</html>
