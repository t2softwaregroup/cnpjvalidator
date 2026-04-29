<?php

namespace T2Group\Cnpjvalidator;

/**
 * @author: Gelvazio Camargo
 * @description: Validador de CNPJ alfanumérico
 * @since   : 2026-04-23
 * @version : 1.1.0
 * @package : T2Group\Cnpjvalidator * 
 */
class CnpjValidator
{

    /**
     * Remove pontuação e converte para maiúsculas.
     *
     * @param mixed $val CNPJ que pode conter pontos, barras ou hífens (não string retorna vazio)
     * @return string CNPJ limpo contendo apenas letras maiúsculas (A-Z) e dígitos (0-9)
     */
    public static function removePontuacaoCnpjAlfaNumerico($val)
    {
        return self::cleanInput($val);
    }

    /**
     * Remove caracteres especiais do CNPJ e converte para maiúsculas.
     *
     * @param mixed $cnpj CNPJ que pode conter pontos, barras ou hífens (não string retorna vazio)
     * @return string CNPJ limpo contendo apenas letras maiúsculas (A-Z) e dígitos (0-9)
     */
    private static function cleanInput($cnpj)
    {
        // Não string: retorna vazio, evitando TypeError com entrada inesperada
        if (!is_string($cnpj)) {
            return '';
        }

        // Remove todos os caracteres que não são letras (A-Za-z) ou dígitos (0-9)
        $cleaned = preg_replace('/[^A-Za-z0-9]/', '', $cnpj);

        // Converte para maiúsculas e retorna string vazia se resultado não for string
        return is_string($cleaned) ? strtoupper($cleaned) : '';
    }

    /**
     * Calcula o dígito verificador baseado na string e pesos fornecidos.
     *
     * @param string $baseString String de entrada (raiz + ordem do CNPJ, sem dígitos verificadores)
     * @param int[] $weights Array contendo os pesos para cada posição
     * @return int Dígito verificador calculado (0-9)
     */
    private static function calculateCheckDigit(string $baseString, array $weights)
    {
        // Inicializa acumulador de soma
        $sum = 0;

        // Obtém o comprimento da string para iteração
        $len = strlen($baseString);

        // Loop através de cada caractere da string
        for ($i = 0; $i < $len; $i++) {
            // Obtém o valor ASCII do caractere e subtrai 48 para converter '0'-'9' em 0-9
            // Multiplica pelo peso correspondente e soma ao total
            $sum += (ord($baseString[$i]) - 48) * $weights[$i];
        }

        // Calcula o resto da divisão por 11
        $remainder = $sum % 11;

        // Se resto é menor que 2, dígito é 0; caso contrário, é 11 - resto
        return $remainder < 2 ? 0 : 11 - $remainder;
    }

    /**
     * Valida se um CNPJ alfanumérico no novo formato é válido.
     *
     * @param mixed $cnpj Entrada que será validada como CNPJ
     * @return bool Verdadeiro se CNPJ é válido, falso caso contrário
     */
    public static function isValid($cnpj)
    {
        // Verifica se a entrada é uma string; retorna falso se não for
        if (!is_string($cnpj)) {
            return false;
        }

        // Remove pontuação e converte para maiúsculas
        $clean = self::cleanInput($cnpj);

        // Valida se o comprimento é exatamente 14 caracteres (12 base + 2 dígitos verificadores)
        if (strlen($clean) !== 14) {
            return false;
        }

        // Rejeita CNPJs com todos os caracteres iguais (ex: AAAAAAAAAAAAAA, 11111111111111)
        // Isso evita CNPJs inválidos ou gerados incorretamente
        if (preg_match('/^([A-Z0-9])\1{13}$/', $clean) === 1) {
            return false;
        }

        // Extrai os primeiros 12 caracteres (raiz + ordem, sem os dígitos verificadores)
        $base = substr($clean, 0, 12);

        // Calcula o primeiro dígito verificador usando pesos específicos [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        $d1 = self::calculateCheckDigit($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        // Calcula o segundo dígito verificador usando a raiz + primeiro dígito e pesos [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        $d2 = self::calculateCheckDigit(
            $base . (string)$d1,
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        // Compara os últimos 2 caracteres do CNPJ com os dígitos calculados
        return substr($clean, 12, 2) === ((string)$d1 . (string)$d2);
    }

    /**
     * Valida e retorna um CNPJ limpo (apenas A-Z0-9 maiúsculas).
     *
     * @param mixed $cnpj CNPJ a ser validado e retornado
     * @return string CNPJ sem pontuação (apenas letras maiúsculas e dígitos)
     * @throws \InvalidArgumentException Se $cnpj não for string ou for inválido
     */
    public static function create($cnpj)
    {
        // Valida se a entrada é uma string
        if (!is_string($cnpj)) {
            // Lança exceção com mensagem em português se não for string
            throw new \InvalidArgumentException('CNPJ Alfanumérico Inválido!');
        }

        // Valida a integridade do CNPJ usando o método isValid()
        if (!self::isValid($cnpj)) {
            // Lança exceção se CNPJ falhar na validação
            throw new \InvalidArgumentException('CNPJ Alfanumérico Inválido!');
        }

        // Retorna o CNPJ limpo (sem pontuação, apenas letras e dígitos maiúsculos)
        return self::cleanInput($cnpj);
    }

    /**
     * Formata CNPJ com máscara padrão XX.XXX.XXX/XXXX-XX.
     *
     * @param mixed $cnpj CNPJ que pode conter ou não pontuação (não string ou tamanho ≠ 14 retorna null)
     * @return string|null CNPJ formatado ou null se entrada inválida
     */
    public static function format($cnpj)
    {
        // Não string ou vazio: retorna null
        if (!is_string($cnpj) || $cnpj === '') {
            return null;
        }

        // Limpa a entrada
        $clean = self::cleanInput($cnpj);

        // Rejeita se não tiver exatamente 14 caracteres
        if (strlen($clean) !== 14) {
            return null;
        }

        // Aplica máscara XX.XXX.XXX/XXXX-XX
        return substr($clean, 0, 2) . '.' . substr($clean, 2, 3) . '.' .
               substr($clean, 5, 3) . '/' . substr($clean, 8, 4) . '-' .
               substr($clean, 12, 2);
    }

    /**
     * Verifica se o CNPJ contém letras (novo formato alfanumérico da Receita Federal).
     *
     * @param mixed $cnpj CNPJ a ser verificado (com ou sem pontuação)
     * @return bool Verdadeiro se CNPJ limpo de 14 chars contiver ao menos uma letra A-Z
     */
    public static function isAlfa($cnpj)
    {
        // Não string: definitivamente não é alfanumérico
        if (!is_string($cnpj)) {
            return false;
        }

        // Limpa a entrada e verifica comprimento
        $clean = self::cleanInput($cnpj);
        if (strlen($clean) !== 14) {
            return false;
        }

        // Verdadeiro se houver ao menos um caractere A-Z
        return (bool) preg_match('/[A-Z]/', $clean);
    }

    /**
     * Formata CNPJ para uso em arquivos CNAB (apenas numérico, 14 dígitos com zeros à esquerda).
     * Lança RuntimeException se o CNPJ contiver letras, pois FEBRABAN não suporta CNPJ alfanumérico.
     *
     * @param mixed $cnpj CNPJ com ou sem pontuação
     * @return string CNPJ numérico com 14 dígitos, preenchido com zeros à esquerda
     * @throws \RuntimeException Se o CNPJ contiver letras (formato alfanumérico não suportado pelo CNAB)
     */
    public static function formatCnab($cnpj)
    {
        // Limpa a entrada mantendo apenas caracteres alfanuméricos e convertendo para maiúsculas
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $cnpj));

        // CNAB não suporta CNPJ alfanumérico: lança exceção se houver letras
        if (preg_match('/[A-Z]/', $clean)) {
            throw new \RuntimeException(
                'CNAB não suporta CNPJ alfanumérico. FEBRABAN ainda não publicou suporte oficial. ' .
                'CNPJ recebido contém letras: ' . $clean
            );
        }

        // Preenche com zeros à esquerda até 14 dígitos
        return str_pad($clean, 14, '0', STR_PAD_LEFT);
    }
}
