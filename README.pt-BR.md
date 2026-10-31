# Renice

[English](README.md) · **Português**

Um e-commerce que nasceu como projeto de faculdade em 2024 e está sendo reconstruído, em público, no padrão de produção de 2026.

> **Status:** a loja e o admin foram totalmente reescritos (Fases 0 a 3), e todos os achados da [auditoria inicial](docs/audit/initial-audit.md) estão resolvidos. Próximo passo: pagamento via Pix e ciclo de vida do pedido (Fase 4). Acompanhe pelo [roadmap](docs/roadmap.md).

![Catálogo de produtos da Renice v1](docs/legacy/images/03-product-catalog.jpg)

## A história

Renice é uma marca fictícia de copos com gelo prontos para consumo. Em junho de 2024 ela virou o trabalho final de duas disciplinas de Sistemas de Informação na Unimontes: uma loja em Laravel com painel administrativo (Programação II) e um banco MySQL baseado em stored procedures e triggers (Banco de Dados II).

Essa versão está congelada na tag [`v1.0-academic`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v1.0-academic). Este repositório agora documenta o trabalho de levá-la de trabalho acadêmico a um código que eu colocaria em produção:

1. **Auditoria:** 28 achados, de escalada de privilégio e SQL injection a regras de negócio escondidas em triggers ([ler a auditoria](docs/audit/initial-audit.md), em inglês).
2. **Plataforma:** Laravel 11 → 13, PHP 8.4+, CI com análise estática e testes.
3. **Camada de dados:** schema versionado e regras de negócio saindo do banco para código testado.
4. **Frontend:** reescrita com Inertia, React e TypeScript.
5. **Comércio:** ciclo de vida do pedido, pagamento via Pix com webhooks e estoque seguro sob concorrência.
6. **Recursos de 2026:** busca semântica, resumo de avaliações com IA, admin em tempo real, API pública e servidor MCP para agentes de IA.

Cada passo é uma issue, um pull request e, quando há um trade-off real, um [registro de decisão de arquitetura](docs/adr/README.md).

## Progresso

| Fase | Status |
|---|---|
| 0 — Fundação | Concluída ([`v2.0.0-alpha.1`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.1)) |
| 1 — Upgrade do framework | Concluída ([`v2.0.0-alpha.2`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.2)) |
| 2 — Redesenho da camada de dados | Concluída ([`v2.0.0-alpha.3`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-alpha.3)) |
| 3 — Reescrita com Inertia + React | Concluída ([`v2.0.0-beta.1`](https://github.com/lopesDilceu/ReniceProject/releases/tag/v2.0.0-beta.1)) |
| 4 — Núcleo de comércio | Planejada |
| 5 — Recursos de destaque | Planejada |
| 6 — Produção e estudo de caso | Planejada |

Os números de antes e depois ficam em [`docs/metrics.md`](docs/metrics.md).

## Documentação

A documentação técnica está em inglês. Veja a [versão em inglês deste README](README.md#documentation) para o índice completo.

## Autores

- **Dilceu Lopes:** aplicação, banco de dados e modernização
- **Renan Iago Figueiredo Silva Ciríaco:** projeto do banco de dados na v1

## Licença

[MIT](LICENSE)
