# CnpjValidator (CNPJ alfanumérico)

**Pacote:** [`t2softwaregroup/cnpjvalidator`](https://packagist.org/packages/t2softwaregroup/cnpjvalidator) — *A simple package to validate CNPJ Alphanumeric.*  
**Tipo:** biblioteca (`library` no `composer.json`).

Biblioteca PHP para **validar** e **normalizar** CNPJ no formato **alfanumérico** adotado pela Receita Federal, incluindo CNPJs **somente numéricos** (caso particular em que todas as posições da base são dígitos). Inclui ainda utilitários opcionais para **formatação de CPF/CNPJ em telas**, **linhas vindas de SQL** e **mascaramento para auditoria (LGPD)** — sem consulta à Receita Federal.

Referência oficial sobre o novo padrão e simulador: [Simulador Nacional de CNPJ — Receita Federal](https://servicos.receitafederal.gov.br/servico/cnpj-alfa/simular).

**Homepage:** https://www.t2group.com.br/

**Palavras-chave (Packagist):** `cnpj`, `validation`, `validator`, `cnpjvalidation`, `cnpjvalidator`

---

## Requisitos

- **PHP:** `^7.1 || ^8.0 || ^8.1 || ^8.2 || ^8.3 || ^8.4` (única dependência de produção em `require`; `^7.1` por causa de tipos anuláveis `?string` no código)
- **Desenvolvimento:** [Pest](https://pestphp.com/) `^4.6` (`require-dev`)

---

## O que a classe faz

A classe `T2SoftwareGroup\Cnpjvalidator\CnpjValidator` concentra regras de **integridade** do CNPJ (tamanho, dígitos verificadores e alguns casos obviamente inválidos). Ela **não** consulta a Receita Federal nem confirma se o CNPJ existe ou está ativo; apenas verifica se a **sequência informada** é coerente com o algoritmo de validação. Os métodos que tratam **CPF** ou **máscara para log** são apenas conveniência de apresentação e armazenamento seguro; **não** validam dígito verificador de CPF.

### Comportamento em resumo

1. **Normalização**  
   Remove pontuação (pontos, barra, hífen etc.), mantém apenas letras `A–Z` e dígitos `0–9`, e converte letras para **maiúsculas**.

2. **Formato**  
   Após a limpeza, o valor deve ter **exatamente 14 caracteres**: 12 da base (raiz + ordem/filial) + **2 dígitos verificadores** (sempre numéricos `0–9`).

3. **Dígitos verificadores**  
   Recalcula os dois dígitos com os **pesos oficiais** do CNPJ (módulo 11). Para cada caractere da base, usa o valor derivado do código (incluindo letras, conforme a regra do CNPJ alfanumérico) e compara com os dois últimos caracteres informados.

4. **Rejeições adicionais**  
   - Entrada que não é **string** (em `isValid` retorna `false`; em `create` lança exceção).  
   - Sequência de **14 caracteres todos iguais** (ex.: `00000000000000` ou `AAAAAAAAAAAAAA`).  
   - Comprimento diferente de 14 após remover a máscara.

---

## Autoload (PSR-4)

Prefixo no `composer.json`: `T2SoftwareGroup\Cnpjvalidator\` → diretório `src/`. Classe principal: `T2SoftwareGroup\Cnpjvalidator\CnpjValidator`.

---

## Métodos públicos

### CNPJ: validação e formato

| Método | Retorno | Descrição |
|--------|---------|-----------|
| `removePontuacaoCnpjAlfaNumerico($val)` | `string` | Remove máscara e sobe letras. Se `$val` não for string, retorna `''`. **Não** valida DV. |
| `normalize(?string $cnpj)` | `?string` | Mesmo efeito que `removePontuacaoCnpjAlfaNumerico` (inclui `null` → `''`). |
| `isValid($cnpj)` | `bool` | `true` se for string, tiver 14 caracteres após limpeza e os dígitos verificadores baterem. |
| `create($cnpj)` | `string` | Valida como `isValid` e retorna CNPJ limpo; senão lança `InvalidArgumentException` (`CNPJ Alfanumérico Inválido!`). |
| `format($cnpj)` | `?string` | Máscara `XX.XXX.XXX/XXXX-XX` ou `null` se inválido. |
| `formatAlfanumerico(?string $cnpj)` | `?string` | Alias de `format`. |
| `formatNumerico(?string $cnpj)` | `?string` | Hoje equivale a `formatAlfanumerico` (mesma máscara alfanumérica). |
| `isAlfa($cnpj)` | `bool` | `true` se, após limpeza, houver 14 caracteres e ao menos uma letra `A–Z`. |
| `formatCnab($cnpj)` | `string` | CNPJ numérico 14 posições (zeros à esquerda). Lança `RuntimeException` se houver letras na base limpa. |

### CPF/CNPJ em telas, busca e auditoria

| Método | Retorno | Descrição |
|--------|---------|-----------|
| `formatCpfOuCnpjParaExibicao(?string $value)` | `?string` | Se após limpeza alfanumérica tiver 14 caracteres, formata como CNPJ; se só dígitos tiverem comprimento 11, formata como CPF; senão devolve o valor original ou `null` se vazio/`null`. **Não** valida CPF/CNPJ. |
| `formatResultadoSqlDocumentos(array $row)` | `array` | Formata chaves comuns (`cpf`, `cpf_cnpj`, `cpf_favorecido`, `cnpj`, `empresa_cnpj`) em uma linha associativa (ex.: resultado de query). |
| `normalizeDocumentoBusca(string $documento)` | `string` | Para busca: 14 alfanuméricos após limpeza → string limpa em maiúsculas; caso contrário → apenas dígitos (ex.: CPF com máscara). |
| `maskForAudit(?string $value)` | `?string` | Máscara parcial para log/armazenamento (LGPD): CNPJ, CPF ou `***`. |

---

## Instalação

```bash
composer require t2softwaregroup/cnpjvalidator
```

*(Em desenvolvimento local, use `repositories` de path ou VCS no `composer.json` do projeto consumidor.)*

---

## Uso

```php
use T2SoftwareGroup\Cnpjvalidator\CnpjValidator;

// Só limpar máscara (não valida)
$limpo = CnpjValidator::removePontuacaoCnpjAlfaNumerico('12.ABC.345/6789-90');
// ex.: '12ABC345678990' (exemplo ilustrativo; o DV precisa estar correto para isValid)

// Validar sem exceção
if (CnpjValidator::isValid('11.444.777/0001-61')) {
    // ...
}

// Validar e obter string normalizada ou falhar
try {
    $cnpjLimpo = CnpjValidator::create('11.444.777/0001-61'); // '11444777000161'
} catch (\InvalidArgumentException $e) {
    // CNPJ inválido ou tipo incorreto
}

// Máscara para exibição
$mascarado = CnpjValidator::format('11444777000161'); // '11.444.777/0001-61'

// CPF ou CNPJ bruto (ex.: coluna SQL) → exibição
$doc = CnpjValidator::formatCpfOuCnpjParaExibicao('12345678901');   // '123.456.789-01'
$cnpjTela = CnpjValidator::formatCpfOuCnpjParaExibicao('11444777000161'); // '11.444.777/0001-61'

// Várias colunas de uma linha
$row = CnpjValidator::formatResultadoSqlDocumentos([
    'nome' => 'Empresa',
    'cpf' => '12345678901',
    'cnpj' => '11444777000161',
]);

// Busca (14 alfanuméricos vs 11 dígitos)
$chave = CnpjValidator::normalizeDocumentoBusca('11.444.777/0001-61'); // '11444777000161'

// Auditoria / log
$audit = CnpjValidator::maskForAudit('11.444.777/0001-61'); // '11.***.***/****-61'
```

---

## Testes

O projeto usa [Pest](https://pestphp.com/). O `composer.json` define `composer test` → `pest`:

```bash
composer test
```

A suíte cobre validação CNPJ, `formatCnab`, utilitários de CPF/CNPJ para exibição, SQL, busca e `maskForAudit`.

No Windows, se `pest` não estiver no `PATH`:

```bash
php vendor/bin/pest
```

---

## Publicação no Composer

Vídeo com orientação de como publicar a lib para uso via Composer:

- [YouTube — publicação de pacote Composer](https://www.youtube.com/watch?v=bFufyOxwSew)

---

## Autores

- Gelvazio Camargo — gelvazio@gmail.com

---

## Licença

MIT (conforme `composer.json`).
