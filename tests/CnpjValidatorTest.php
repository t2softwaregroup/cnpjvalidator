<?php

use T2SoftwareGroup\Cnpjvalidator\CnpjValidator;

/**
 * CNPJs válidos (DV módulo 11, critério CNPJ alfanumérico) usados como massa de teste.
 *
 * @return array<int, array{0: string}>
 */
function cnpjDataset10Numericos(): array
{
    return array_map(
        static fn (string $c) => [$c],
        [
            '11222333000181',
            '11222333000262',
            '33444555000181',
            '33444555000343',
            '55666777000181',
            '55666777000424',
            '77888999000181',
            '77888999000505',
            '99000111000165',
            '99000111000670',
        ]
    );
}

/**
 * @return array<int, array{0: string}>
 */
function cnpjDataset10Alfanumericos(): array
{
    return array_map(
        static fn (string $c) => [$c],
        [
            '12ABC345000188',
            '12ABC345000269',
            '34DE5678000154',
            '34DE5678000235',
            '56FG7890000107',
            '56FG7890000280',
            '78HI9012000160',
            '78HI9012000241',
            '90JK1234000148',
            '90JK1234000229',
        ]
    );
}

/**
 * Cinco raízes × filiais 0001, 0002, 0003 (15 CNPJs numéricos).
 *
 * @return array<int, array{0: string}>
 */
function cnpjDatasetNumericosCom3Filiais(): array
{
    return array_map(
        static fn (string $c) => [$c],
        [
            '11444777000161',
            '11444777000242',
            '11444777000323',
            '22333444000181',
            '22333444000262',
            '22333444000343',
            '33555666000165',
            '33555666000246',
            '33555666000327',
            '44777888000149',
            '44777888000220',
            '44777888000300',
            '55999001000183',
            '55999001000264',
            '55999001000345',
        ]
    );
}

/**
 * Cinco raízes alfanuméricas × filiais 0001, 0002, 0003 (15 CNPJs).
 *
 * @return array<int, array{0: string}>
 */
function cnpjDatasetAlfanumericosCom3Filiais(): array
{
    return array_map(
        static fn (string $c) => [$c],
        [
            'AB123456000110',
            'AB123456000209',
            'AB123456000381',
            'CD234567000136',
            'CD234567000217',
            'CD234567000306',
            'EF345678000152',
            'EF345678000233',
            'EF345678000314',
            'GH456789000179',
            'GH456789000250',
            'GH456789000330',
            'IJ567890000130',
            'IJ567890000210',
            'IJ567890000300',
        ]
    );
}

describe('removePontuacaoCnpjAlfaNumerico', function () {
    test('remove pontuação e converte para maiúsculas', function () {
        expect(CnpjValidator::removePontuacaoCnpjAlfaNumerico('12.aBc-34/5'))
            ->toBe('12ABC345');
    });

    test('retorna string vazia para não-string', function ($input) {
        expect(CnpjValidator::removePontuacaoCnpjAlfaNumerico($input))->toBe('');
    })->with([
        [null],
        [12345],
        [[]],
        [new stdClass],
    ]);

    test('aceita apenas letras e dígitos após limpeza', function () {
        expect(CnpjValidator::removePontuacaoCnpjAlfaNumerico('ab#12@cd'))
            ->toBe('AB12CD');
    });
});

describe('isValid', function () {
    test('rejeita não-string', function ($input) {
        expect(CnpjValidator::isValid($input))->toBeFalse();
    })->with([
        [null],
        [11444777000161],
        [[]],
        [false],
    ]);

    test('rejeita comprimento diferente de 14 após limpeza', function ($cnpj) {
        expect(CnpjValidator::isValid($cnpj))->toBeFalse();
    })->with([
        '',
        '123',
        '1144477700016',
        '114447770001611',
    ]);

    test('rejeita CNPJ com todos os caracteres iguais', function ($cnpj) {
        expect(CnpjValidator::isValid($cnpj))->toBeFalse();
    })->with([
        '00000000000000',
        '11111111111111',
        'AAAAAAAAAAAAAA',
    ]);

    test('aceita CNPJ numérico válido com ou sem máscara', function () {
        expect(CnpjValidator::isValid('11444777000161'))->toBeTrue();
        expect(CnpjValidator::isValid('11.444.777/0001-61'))->toBeTrue();
    });

    test('aceita CNPJ alfanumérico válido', function () {
        expect(CnpjValidator::isValid('12ABC345678990'))->toBeTrue();
    });

    test('rejeita dígitos verificadores incorretos', function () {
        expect(CnpjValidator::isValid('11444777000162'))->toBeFalse();
        expect(CnpjValidator::isValid('12ABC345678991'))->toBeFalse();
    });

    test('aceita lista de 10 CNPJs numéricos válidos', function (string $cnpj) {
        expect(CnpjValidator::isValid($cnpj))->toBeTrue();
    })->with(cnpjDataset10Numericos());

    test('aceita lista de 10 CNPJs alfanuméricos válidos', function (string $cnpj) {
        expect(CnpjValidator::isValid($cnpj))->toBeTrue();
    })->with(cnpjDataset10Alfanumericos());

    test('aceita 15 CNPJs numéricos (5 raízes × 3 filiais)', function (string $cnpj) {
        expect(CnpjValidator::isValid($cnpj))->toBeTrue();
    })->with(cnpjDatasetNumericosCom3Filiais());

    test('aceita 15 CNPJs alfanuméricos (5 raízes × 3 filiais)', function (string $cnpj) {
        expect(CnpjValidator::isValid($cnpj))->toBeTrue();
    })->with(cnpjDatasetAlfanumericosCom3Filiais());
});

describe('create', function () {
    test('retorna CNPJ limpo quando válido', function () {
        expect(CnpjValidator::create('11.444.777/0001-61'))
            ->toBe('11444777000161');
        expect(CnpjValidator::create('12ABC345678990'))
            ->toBe('12ABC345678990');
    });

    test('create retorna o mesmo valor limpo para CNPJs da lista numérica (10)', function (string $cnpj) {
        expect(CnpjValidator::create($cnpj))->toBe($cnpj);
    })->with(cnpjDataset10Numericos());

    test('create retorna o mesmo valor limpo para CNPJs da lista alfanumérica (10)', function (string $cnpj) {
        expect(CnpjValidator::create($cnpj))->toBe($cnpj);
    })->with(cnpjDataset10Alfanumericos());

    test('create para CNPJs numéricos com filiais (15)', function (string $cnpj) {
        expect(CnpjValidator::create($cnpj))->toBe($cnpj);
    })->with(cnpjDatasetNumericosCom3Filiais());

    test('create para CNPJs alfanuméricos com filiais (15)', function (string $cnpj) {
        expect(CnpjValidator::create($cnpj))->toBe($cnpj);
    })->with(cnpjDatasetAlfanumericosCom3Filiais());

    test('lança InvalidArgumentException quando não é string', function () {
        expect(fn () => CnpjValidator::create(null))
            ->toThrow(InvalidArgumentException::class, 'CNPJ Alfanumérico Inválido!');
    });

    test('lança InvalidArgumentException quando CNPJ é inválido', function () {
        expect(fn () => CnpjValidator::create('11444777000162'))
            ->toThrow(InvalidArgumentException::class, 'CNPJ Alfanumérico Inválido!');
    });
});

describe('format', function () {
    test('formata CNPJ numérico com máscara', function () {
        expect(CnpjValidator::format('11444777000161'))
            ->toBe('11.444.777/0001-61');
    });

    test('formata CNPJ numérico já mascarado', function () {
        expect(CnpjValidator::format('11.444.777/0001-61'))
            ->toBe('11.444.777/0001-61');
    });

    test('formata CNPJ alfanumérico com máscara', function () {
        expect(CnpjValidator::format('12ABC345000188'))
            ->toBe('12.ABC.345/0001-88');
    });

    test('formata CNPJ alfanumérico já mascarado', function () {
        expect(CnpjValidator::format('12.ABC.345/0001-88'))
            ->toBe('12.ABC.345/0001-88');
    });

    test('retorna null para não-string', function ($input) {
        expect(CnpjValidator::format($input))->toBeNull();
    })->with([
        [null],
        [12345],
        [[]],
    ]);

    test('retorna null para string vazia', function () {
        expect(CnpjValidator::format(''))->toBeNull();
    });

    test('retorna null para comprimento diferente de 14 após limpeza', function ($cnpj) {
        expect(CnpjValidator::format($cnpj))->toBeNull();
    })->with([
        '123',
        '1144477700016',
        '114447770001611',
    ]);
});

describe('isAlfa', function () {
    test('retorna true para CNPJ alfanumérico limpo', function () {
        expect(CnpjValidator::isAlfa('12ABC345000188'))->toBeTrue();
    });

    test('retorna true para CNPJ alfanumérico com máscara', function () {
        expect(CnpjValidator::isAlfa('12.ABC.345/0001-88'))->toBeTrue();
    });

    test('retorna false para CNPJ apenas numérico', function () {
        expect(CnpjValidator::isAlfa('11444777000161'))->toBeFalse();
    });

    test('retorna false para não-string', function ($input) {
        expect(CnpjValidator::isAlfa($input))->toBeFalse();
    })->with([
        [null],
        [12345],
        [[]],
    ]);

    test('retorna false para comprimento diferente de 14 após limpeza', function () {
        expect(CnpjValidator::isAlfa('12ABC'))->toBeFalse();
    });
});

describe('formatCnab', function () {
    test('formata CNPJ numérico sem máscara', function () {
        expect(CnpjValidator::formatCnab('11222333000181'))
            ->toBe('11222333000181');
    });

    test('formata CNPJ numérico com máscara', function () {
        expect(CnpjValidator::formatCnab('11.444.777/0001-61'))
            ->toBe('11444777000161');
    });

    test('preenche com zeros à esquerda se CNPJ for curto', function () {
        expect(CnpjValidator::formatCnab('1234'))
            ->toBe('00000000001234');
    });

    test('lança RuntimeException para CNPJ alfanumérico', function () {
        expect(fn () => CnpjValidator::formatCnab('12ABC345000188'))
            ->toThrow(\RuntimeException::class, 'CNAB não suporta CNPJ alfanumérico');
    });

    test('lança RuntimeException para CNPJ alfanumérico com máscara', function () {
        expect(fn () => CnpjValidator::formatCnab('12.ABC.345/0001-88'))
            ->toThrow(\RuntimeException::class, 'CNAB não suporta CNPJ alfanumérico');
    });

    test('lança RuntimeException para letras minúsculas', function () {
        expect(fn () => CnpjValidator::formatCnab('12abc345000188'))
            ->toThrow(\RuntimeException::class, 'CNAB não suporta CNPJ alfanumérico');
    });
});

describe('normalize', function () {
    test('equivale a removePontuacaoCnpjAlfaNumerico para string', function () {
        expect(CnpjValidator::normalize('12.aBc-34/5'))
            ->toBe(CnpjValidator::removePontuacaoCnpjAlfaNumerico('12.aBc-34/5'))
            ->toBe('12ABC345');
    });

    test('retorna string vazia para null', function () {
        expect(CnpjValidator::normalize(null))->toBe('');
    });
});

describe('formatAlfanumerico', function () {
    test('delega para format com CNPJ válido', function () {
        expect(CnpjValidator::formatAlfanumerico('11444777000161'))
            ->toBe('11.444.777/0001-61');
        expect(CnpjValidator::formatAlfanumerico('12ABC345000188'))
            ->toBe('12.ABC.345/0001-88');
    });

    test('retorna null quando format retorna null', function () {
        expect(CnpjValidator::formatAlfanumerico(null))->toBeNull();
        expect(CnpjValidator::formatAlfanumerico(''))->toBeNull();
        expect(CnpjValidator::formatAlfanumerico('123'))->toBeNull();
    });
});

describe('formatNumerico', function () {
    test('comporta-se como formatAlfanumerico', function () {
        expect(CnpjValidator::formatNumerico('11444777000161'))
            ->toBe(CnpjValidator::formatAlfanumerico('11444777000161'));
    });
});

describe('formatCpfOuCnpjParaExibicao', function () {
    test('retorna null para null ou string vazia', function ($input) {
        expect(CnpjValidator::formatCpfOuCnpjParaExibicao($input))->toBeNull();
    })->with([
        [null],
        [''],
    ]);

    test('formata CNPJ numérico e alfanumérico com 14 posições após limpeza', function () {
        expect(CnpjValidator::formatCpfOuCnpjParaExibicao('11444777000161'))
            ->toBe('11.444.777/0001-61');
        expect(CnpjValidator::formatCpfOuCnpjParaExibicao('12.ABC.345/0001-88'))
            ->toBe('12.ABC.345/0001-88');
    });

    test('formata CPF com 11 dígitos', function () {
        expect(CnpjValidator::formatCpfOuCnpjParaExibicao('12345678901'))
            ->toBe('123.456.789-01');
        expect(CnpjValidator::formatCpfOuCnpjParaExibicao('123.456.789-01'))
            ->toBe('123.456.789-01');
    });

    test('devolve o valor original se não for CPF nem CNPJ pelo critério de tamanho', function () {
        expect(CnpjValidator::formatCpfOuCnpjParaExibicao('12345'))->toBe('12345');
        expect(CnpjValidator::formatCpfOuCnpjParaExibicao('ABC'))->toBe('ABC');
    });
});

describe('formatResultadoSqlDocumentos', function () {
    test('formata chaves de CPF e CNPJ conhecidas e ignora ausentes ou vazias', function () {
        $row = [
            'nome' => 'X',
            'cpf' => '12345678901',
            'cnpj' => '11444777000161',
            'outro' => '99',
            'vazio' => '',
        ];
        $out = CnpjValidator::formatResultadoSqlDocumentos($row);
        expect($out['nome'])->toBe('X');
        expect($out['cpf'])->toBe('123.456.789-01');
        expect($out['cnpj'])->toBe('11.444.777/0001-61');
        expect($out['outro'])->toBe('99');
        expect($out['vazio'])->toBe('');
    });

    test('formata cpf_cnpj, cpf_favorecido e empresa_cnpj', function () {
        $row = [
            'cpf_cnpj' => '12ABC345000188',
            'cpf_favorecido' => '529.982.247-25',
            'empresa_cnpj' => '11.444.777/0001-61',
        ];
        $out = CnpjValidator::formatResultadoSqlDocumentos($row);
        expect($out['cpf_cnpj'])->toBe('12.ABC.345/0001-88');
        expect($out['cpf_favorecido'])->toBe('529.982.247-25');
        expect($out['empresa_cnpj'])->toBe('11.444.777/0001-61');
    });

    test('não altera cnpj quando formatAlfanumerico retorna null', function () {
        $row = ['cnpj' => '123'];
        $out = CnpjValidator::formatResultadoSqlDocumentos($row);
        expect($out['cnpj'])->toBe('123');
    });
});

describe('normalizeDocumentoBusca', function () {
    test('retorna 14 alfanuméricos em maiúsculas para CNPJ', function () {
        expect(CnpjValidator::normalizeDocumentoBusca('12.ABC.345/0001-88'))
            ->toBe('12ABC345000188');
    });

    test('retorna apenas dígitos para documento que não fecha 14 alfanuméricos', function () {
        expect(CnpjValidator::normalizeDocumentoBusca('123.456.789-01'))->toBe('12345678901');
        expect(CnpjValidator::normalizeDocumentoBusca('1144477700016'))->toBe('1144477700016');
    });
});

describe('maskForAudit', function () {
    test('retorna null para null ou vazio', function ($input) {
        expect(CnpjValidator::maskForAudit($input))->toBeNull();
    })->with([
        [null],
        [''],
    ]);

    test('mascara CNPJ com 14 caracteres após normalização', function () {
        expect(CnpjValidator::maskForAudit('11.444.777/0001-61'))
            ->toBe('11.***.***/****-61');
        expect(CnpjValidator::maskForAudit('12.ABC.345/0001-88'))
            ->toBe('12.***.***/****-88');
    });

    test('mascara CPF com 11 dígitos', function () {
        expect(CnpjValidator::maskForAudit('123.456.789-01'))
            ->toBe('123.***.***-01');
    });

    test('retorna asteriscos para valor que não é CPF nem CNPJ pelo critério usado', function () {
        expect(CnpjValidator::maskForAudit('12345'))->toBe('***');
        expect(CnpjValidator::maskForAudit('texto'))->toBe('***');
    });
});
