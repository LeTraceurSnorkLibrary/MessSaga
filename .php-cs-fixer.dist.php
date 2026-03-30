<?php

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__)
    ->exclude([
        'storage',
    ]);

return new PhpCsFixer\Config()
    ->setRules([
        '@PER-CS'                      => true,
        '@PHP8x4Migration'             => true,
        'blank_line_after_opening_tag' => false,
        'binary_operator_spaces'       => [
            'default'   => 'align_single_space_minimal',
            'operators' => [
                '='  => 'align_single_space',
                '=>' => 'align_single_space',
                '??' => 'single_space',
            ],
        ],
        'cast_spaces'                  => false,
        'no_blank_lines_after_phpdoc'  => false,
        'single_line_empty_body'       => false,
    ])
    ->setFinder($finder);
