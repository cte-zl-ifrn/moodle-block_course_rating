# Plugin Blocks - Course Rating

Plugin de avaliação de curso em bloco

### Tabelas do plugin

| Tabela de classificações | mdl_course_rating |               |
|------------------------- | ----------------- | ------------  |
| id        | Int          | Chave primária                    |
| rating    | Int          | Nota da classificação             |
| message   | Text         | Comentário da classificação       |
| courseid  | Int          | ID do curso da classificação      |
| createdat | datetime     | Data de Criação da classificação  |
| updatedat | datetime     | Data de Edição da classificação   |

| Tabela de historico de classificações | course_rating_history       |           |
|------ | ----------------- | --------- |
| id        | Int | Chave primária      |
| rating    | Int | Nota da classificação   |
| message   | Text | Comentário da classificação    |
| originid  | Int | ID da classificação original    |
| createdat | datetime  | Data de Criação da classificação  |

### Configuração do plugin

1. Configuração padrão: Exibir formulário para avaliar o curso apenas após ele ser marcado como concluído.

 ![](/pix/config-02.png)

2. Configuração opcional: Exibir o formulário para avaliar o curso a qualquer momento.

 ![](/pix/config-01.png)

 