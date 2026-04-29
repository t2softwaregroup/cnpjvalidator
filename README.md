# CnpjValidator (CNPJ alfanumérico)

**Pacote:** [`t2group/cnpjvalidator`](https://packagist.org/packages/t2group/cnpjvalidator) — *A simple package to validate CNPJ Alphanumeric.*  
**Tipo:** biblioteca (`library` no `composer.json`).

Biblioteca PHP para **validar** e **normalizar** CNPJ no formato **alfanumérico** adotado pela Receita Federal, incluindo CNPJs **somente numéricos** (caso particular em que todas as posições da base são dígitos).

Referência oficial sobre o novo padrão e simulador: [Simulador Nacional de CNPJ — Receita Federal](https://servicos.receitafederal.gov.br/servico/cnpj-alfa/simular).

**Homepage:** https://www.t2group.com.br/

**Palavras-chave (Packagist):** `cnpj`, `validation`, `validator`, `cnpjvalidation`, `cnpjvalidator`

---

## Requisitos

- **PHP:** `^7.0 || ^8.0 || ^8.1 || ^8.2 || ^8.3 || ^8.4` (única dependência de produção em `require`)
- **Desenvolvimento:** [Pest](https://pestphp.com/) `^4.6` (`require-dev`)

---

## O que a classe faz

A classe `T2Group\Cnpjvalidator\CnpjValidator` concentra regras de **integridade** do número (tamanho, dígitos verificadores e alguns casos obviamente inválidos). Ela **não** consulta a Receita Federal nem confirma se o CNPJ existe ou está ativo; apenas verifica se a **sequência informada** é coerente com o algoritmo de validação.

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

## Métodos públicos

| Método | Retorno | Descrição |
|--------|---------|-----------|
| `removePontuacaoCnpjAlfaNumerico($val)` | `string` | Só normaliza: remove máscara e sobe letras. Se `$val` não for string, retorna `''`. **Não** valida DV. |
| `isValid($cnpj)` | `bool` | `true` se for string, tiver 14 caracteres válidos após limpeza e os dígitos verificadores baterem; caso contrário `false`. |
| `create($cnpj)` | `string` | Igual à validação de `isValid`, mas retorna o CNPJ **limpo** (14 caracteres, maiúsculas). Se não for string ou for inválido, lança `InvalidArgumentException` com a mensagem `CNPJ Alfanumérico Inválido!`. |

---

## Instalação

```bash
composer require t2group/cnpjvalidator
```

*(Em desenvolvimento local, use `repositories` de path ou VCS no `composer.json` do projeto consumidor.)*

---

## Uso

```php
use T2Group\Cnpjvalidator\CnpjValidator;

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
```

---

## Testes

O projeto usa [Pest](https://pestphp.com/). O script definido no `composer.json` é `pest`:

```bash
composer test
```

No Windows, se o comando `pest` não estiver no `PATH`, use:

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
