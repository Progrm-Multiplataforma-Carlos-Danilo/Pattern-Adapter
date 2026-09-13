<?php

require_once __DIR__ . '/vendor/autoload.php';

use DesignPatterns\Structural\Adapter\Book;
use DesignPatterns\Structural\Adapter\PaperBook;
use DesignPatterns\Structural\Adapter\Kindle;
use DesignPatterns\Structural\Adapter\EBookAdapter;

function lerLivro(Book $livro, string $descricao)
{
    echo "========================================\n";
    echo "Testando: $descricao\n";
    echo "========================================\n";
    
    $livro->open();
    echo "1. Livro aberto na página: " . $livro->getPage() . "\n";
    
    $livro->turnPage();
    echo "2. Página virada! Página atual: " . $livro->getPage() . "\n\n";
}

// 1. Testando Livro Físico (implementação nativa de Book)
$livroFisico = new PaperBook();
lerLivro($livroFisico, "Livro de Papel (PaperBook)");

// 2. Testando Kindle (incompatível) adaptado pelo EBookAdapter
$kindle = new Kindle();
$kindleAdaptado = new EBookAdapter($kindle);
lerLivro($kindleAdaptado, "Kindle usando EBookAdapter");
