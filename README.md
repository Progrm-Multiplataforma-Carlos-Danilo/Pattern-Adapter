# Adapter Pattern — Book / EBook (PHP)

Implementação do padrão de projeto estrutural **Adapter**, aplicado ao cenário
de um leitor de livros (`Book`) que precisa funcionar com um leitor digital de
terceiros (`Kindle`), cuja interface (`EBook`) é incompatível.

Baseado no exemplo `Structural/Adapter` do repositório
[DesignPatternsPHP](https://github.com/DesignPatternsPHP/DesignPatternsPHP).

## Sumário

- [Objetivo](#objetivo)
- [Passo 1 — Clonagem do repositório base](#passo-1--clonagem-do-repositório-base)
- [Passo 2 — Instalação de dependências](#passo-2--instalação-de-dependências)
- [Passo 3 — Mapeamento do domínio](#passo-3--mapeamento-do-domínio)
- [Passo 4 — Identificação do conflito](#passo-4--identificação-do-conflito)
- [Passo 5 — Criação da classe adaptadora](#passo-5--criação-da-classe-adaptadora)
- [Passo 6 — Implementação do contrato e composição](#passo-6--implementação-do-contrato-e-composição)
- [Passo 7 — Testes](#passo-7--testes)
- [Como executar](#como-executar)
- [Estrutura final do projeto](#estrutura-final-do-projeto)

## Objetivo

Permitir que o código cliente, que só conhece a interface `Book`, consiga
utilizar um `Kindle` (que implementa `EBook`, com métodos de nomes e retornos
diferentes) **sem alterar nem o cliente, nem o `Kindle`**, criando uma classe
adaptadora que traduz uma interface na outra por **composição**.

## Passo 1 — Clonagem do repositório base

```bash
git clone https://github.com/DesignPatternsPHP/DesignPatternsPHP.git
cd DesignPatternsPHP
```

O diretório de interesse para esta atividade é `Structural/Adapter`.

## Passo 2 — Instalação de dependências

```bash
composer install
```

Isso instala o PHPUnit (usado para rodar os testes automatizados do padrão).

## Passo 3 — Mapeamento do domínio

Análise dos componentes existentes em `Structural/Adapter`:

| Arquivo | Papel | Descrição |
|---|---|---|
| [`Book.php`](Structural/Adapter/Book.php) | **Target** | Interface esperada pelo cliente: `open()`, `turnPage()`, `getPage(): int` |
| [`PaperBook.php`](Structural/Adapter/PaperBook.php) | Implementação concreta do Target | Implementa `Book` diretamente, sem necessidade de adaptação |
| [`EBook.php`](Structural/Adapter/EBook.php) | **Adaptee (interface)** | Interface do subsistema externo: `unlock()`, `pressNext()`, `getPage(): array` (retorna `[paginaAtual, totalPaginas]`) |
| [`Kindle.php`](Structural/Adapter/Kindle.php) | **Adaptee (implementação)** | Simula um leitor digital de terceiros que implementa `EBook`, com nomenclatura própria e incompatível com `Book` |

## Passo 4 — Identificação do conflito

O código cliente só sabe conversar com objetos do tipo `Book`
(`open()`, `turnPage()`, `getPage(): int`). Um `Kindle`, porém, expõe
`unlock()`, `pressNext()` e `getPage(): array` — nomes diferentes **e** um
tipo de retorno diferente (`array` em vez de `int`).

```php
$book = new Kindle();
$book->open(); // ❌ Erro: método open() não existe em Kindle
```

Não é possível usar `Kindle` no lugar de `Book` sem alterar uma das duas
classes — o que não é desejável, pois `Kindle` representa uma biblioteca de
terceiros e o cliente já depende de `Book`.

**Solução:** criar uma classe adaptadora que implemente `Book` (para o
cliente continuar funcionando sem mudanças) e que, por dentro, delegue as
chamadas para uma instância de `EBook` recebida via injeção de dependência
(composição), traduzindo cada chamada.

## Passo 5 — Criação da classe adaptadora

Arquivo criado: [`Structural/Adapter/EBookAdapter.php`](Structural/Adapter/EBookAdapter.php).

## Passo 6 — Implementação do contrato e composição

A classe `EBookAdapter`:

- **implementa `Book`** — mantém compatibilidade com o cliente (Target);
- **recebe uma instância de `EBook` por injeção de dependência no
  construtor** — composição, em vez de herança;
- **traduz cada chamada**:
  - `open()` → delega para `EBook::unlock()`
  - `turnPage()` → delega para `EBook::pressNext()`
  - `getPage(): int` → chama `EBook::getPage()` (que devolve `[pagina, total]`)
    e retorna apenas o primeiro elemento (`[0]`), adaptando o tipo de retorno
    de `array` para `int`.

```php
class EBookAdapter implements Book
{
    public function __construct(protected EBook $eBook)
    {
    }

    public function open()
    {
        $this->eBook->unlock();
    }

    public function turnPage()
    {
        $this->eBook->pressNext();
    }

    public function getPage(): int
    {
        return $this->eBook->getPage()[0];
    }
}
```

Com isso, o cliente passa a usar um `Kindle` exatamente como usaria um
`PaperBook`:

```php
$book = new EBookAdapter(new Kindle());
$book->open();
$book->turnPage();
echo $book->getPage(); // 2
```

## Passo 7 — Testes

Testes automatizados em
[`Structural/Adapter/Tests/AdapterTest.php`](Structural/Adapter/Tests/AdapterTest.php),
cobrindo:

1. `PaperBook` funcionando normalmente (caminho sem adaptação);
2. `Kindle` sendo utilizado por meio de `EBookAdapter` como se fosse um
   `Book` comum, validando a tradução das chamadas e do tipo de retorno.

## Como executar

```bash
composer install
composer test
# ou diretamente:
vendor/bin/phpunit

Para testar as classes:
php teste.php
```

## Estrutura final do projeto

```
adapter-pattern-ebook/
├── composer.json
├── phpunit.xml.dist
└── Structural/
    └── Adapter/
        ├── Book.php            # Target
        ├── PaperBook.php       # Implementação concreta do Target
        ├── EBook.php           # Adaptee (interface)
        ├── Kindle.php          # Adaptee (implementação / sistema externo)
        ├── EBookAdapter.php    # Adapter — foco desta atividade
        └── Tests/
            └── AdapterTest.php
```

---

Baseado no projeto [DesignPatternsPHP](https://github.com/DesignPatternsPHP/DesignPatternsPHP) (licença MIT).
