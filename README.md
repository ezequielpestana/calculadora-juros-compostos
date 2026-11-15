# Calculadora de juros compostos

Projeto em PHP com estilos gerados por Tailwind CSS e Vite.

## Requisitos

- PHP 8.0 ou superior, disponível no terminal.
- Node.js 22.12 ou superior na linha 22 (a versão instalada do Vite também aceita Node 20.19+ ou versões superiores a 22).
- npm, incluído na instalação do Node.js.
- Git, para clonar o repositório.

Não é necessário instalar Composer ou configurar banco de dados.

## Instalar as bibliotecas

Depois de clonar o repositório, abra um terminal na pasta que contém `index.php` e `package.json` e execute:

```sh
npm ci
```

Esse comando instala Vite, Tailwind CSS e o plugin `@tailwindcss/vite` nas versões registradas em `package-lock.json`. Mantenha `package.json` e `package-lock.json` no Git; a pasta `node_modules/` é criada localmente.

No PowerShell, se a execução de `npm.ps1` estiver bloqueada, use `npm.cmd` no lugar de `npm` nos comandos deste guia.

## Executar localmente com PHP

Na raiz do projeto, gere os estilos e inicie o servidor local:

```sh
npm run build
php -S 127.0.0.1:8000
```

Abra <http://127.0.0.1:8000>. Mantenha o terminal do PHP aberto; para encerrar, pressione `Ctrl+C`.

Nesse modo, mantenha o Vite desligado na porta 5173. O PHP detecta essa porta e, se ela estiver ocupada, tenta carregar os recursos de desenvolvimento em vez dos arquivos de `dist/`.

Depois de alterar estilos ou classes Tailwind no PHP, execute `npm run build` novamente e atualize a página. A pasta `dist/` é gerada pelo build e não é enviada ao Git.

## Desenvolver com Laragon e atualização automática

A configuração atual do Vite permite a origem `http://calculadora.test`.

1. Coloque o projeto em `C:\laragon\www\calculadora`.
2. Inicie o servidor web do Laragon e habilite o host virtual `calculadora.test` para essa pasta. Recarregue o Laragon se necessário.
3. Na raiz do projeto, execute `npm ci`, caso ainda não tenha instalado as dependências.
4. Execute:

   ```sh
   npm run dev
   ```

5. Abra <http://calculadora.test> e mantenha o terminal do Vite aberto.

O Laragon executa o PHP; o Vite fornece os estilos na porta 5173. Acesse a aplicação pelo endereço do Laragon. Alterações no PHP podem exigir atualizar a página manualmente.

Para usar outro domínio com o Vite, ajuste `server.cors.origin` em `vite.config.mjs` para a origem exata da aplicação e reinicie `npm run dev`.

Para executar no Laragon sem o Vite, encerre `npm run dev` com `Ctrl+C`, execute `npm run build` e atualize a página.

## Arquivos versionados

O repositório mantém o código PHP, os arquivos de `src/`, a configuração do Vite e os arquivos `package.json` e `package-lock.json`. Dependências, builds, logs e configurações locais ficam fora do versionamento conforme o `.gitignore`.

Em uma nova cópia do projeto, execute sempre `npm ci` e depois `npm run build` ou `npm run dev`, conforme o modo escolhido acima.
