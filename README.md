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

 
 ### Compilação de CSS e JS

Dentro do diretório `amd/src` existe o package.json e gulp

1. instalar as dependencias do gulp:

    `npm install`

2. Rodar o script gulp

    `npm run minify`

### Principais arquivos:

1. **version.php**
- Versão do aplicativo, incrementar ao realizar uma alteração para o moodle entender que houve uma mudança

2. **edit_form.php**
- Arquivo com a configuração do bloco, as duas opções atualmente são: "Após concluir o curso" e "Enquanto estiver cursando"
Quando o bloco é adicionado essa configuração ficará vazia e por padrão o bloco utilizará o "Após concluir o curso"

3. **block_course_rating.php**
- Arquivo com formulário e exibição do bloco de avaliação dentro do curso

4. **endpoint.php**
- Arquivo para injetar HMTL na pagina inicial do curso com as avaliações totais e avaliações de usuários

