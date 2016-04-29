Introdução
------------
Open Source Point of Sale é um ponto baseado na Web do sistema de venda escrito na linguagem PHP.
Ele usa MySQL como o back-end de armazenamento de dados e tem uma interface de usuário simples.

Esta é a última versão 3.0.0 e ele é baseado no Bootstrap 3 usando Bootswatch tema categoricamente como padrão, e CodeIgniter 3.0.6.

Badges
------
[![Build Status](https://travis-ci.org/jekkos/opensourcepos.svg?branch=master)](https://travis-ci.org/jekkos/opensourcepos)

Manter a máquina Rodando
------------------------
Se você gosta do projeto, e você está fazendo dinheiro com isso em uma base diária, em seguida, considerar a compra-me um café que eu possa manter adicionando funcionalidades.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8)

Requisitos do servidor
-------------------
PHP versão 5.5 ou mais recente é recomendado, mas PHP 7.x não é totalmente suportado ainda.

relatar bugs
--------------
Desde OSPOS 3.0.0 é uma versão em desenvolvimento, por favor, certifique-se sempre executar o script de atualização de banco de dados 2.4_to_3.0.sql mais recente.
Por favor, NÃO postar questões se você não tiver feito isso antes de executar OSPOS 3.0.
Certifique-se também de ter actualizado todos os arquivos do mais recente mestre.

relatórios de bugs deve seguir este esquema:

1. Nome do sistema operacional e versão em execução seu servidor Web (por exemplo Linux Ubuntu 15.0)
2. Nome Web Server e versão (por exemplo Apache 2.4)
3. nome de banco de dados e versão (por exemplo, MySQL 5.6)
3. versão do PHP (por exemplo PHP 5.5)
4. idioma selecionado no OSPOS (por exemplo, Inglês, Espanhol)
5. Qualquer configuração de OSPOS que você mudou
6. etapas exatas para reproduzir o (caso de teste) problema

Se a informação acima não é fornecido na íntegra, o problema será marcado como pendente.
Se as informações não forem fornecidas dentro de uma semana vamos fechar o seu problema.

Instalação
------------
1. Crie / localizar um novo banco de dados mysql para instalar ponto de código aberto de venda em
2. Execute o banco de dados de arquivo / database.sql para criar as tabelas necessárias
3. Descompacte e faça o upload Abrir ponto de origem de arquivos de venda ao servidor web
4. Aplicação copiar / config / database.php.tmpl para application / config / database.php
5. Modificar application / config / database.php para se conectar ao seu banco de dados
chave de criptografia / config / config.php 6. aplicação Modificar com seu próprio
7. Vá para o seu ponto de venda instalar através do browser
8. entre usando
nome de usuário: admin
senha: pointofsale
9. Aprecie
FAQ
---
Se uma página em branco (status HTTP 500) mostra após a conclusão de busca ou geração de recebimento, em seguida, verifique a presença php5-gd na sua instalação php. No windows o check-in php.ini se a lib está instalado. No Ubuntu problema sudo `apt-get install php5-gd`. Também dê uma olhada na Dockerfile para obter uma lista completa de pacotes recomendados.

13/01/2016: instalar usando Docker
--------------------------------
A partir de agora ospos podem ser implantados usando Docker em Linux, Mac ou Windows. Esta configuração reduz drasticamente o número de possíveis problemas como toda a configuração está agora feito em um Dockerfile. Docker roda nativamente no Mac e Linux, mas vai exigir mais sobrecarga no Windows. Por favor consulte a documentação janela de encaixe para obter instruções sobre como configurá-lo em sua plataforma.

Para criar e executar a imagem, emitir comandos seguinte em um terminal com janela de encaixe instalada

    docker build -t me/ospos https://github.com/jekkos/opensourcepos.git
    docker run -d -p 80:80 me/ospos

Docker irá clonar o último mestre na imagem e começar uma pilha LAMP com a aplicação configurada. Se você gosta de persistirem as alterações nesta instalar, em seguida, você pode usar dois recipientes de dados janela de encaixe para armazenar as alterações de banco de dados e sistema de arquivos. Neste caso, você precisará seguinte comando (primeiro única vez)

    docker run -d -v /app --name="ospos" -v /var/lib/mysql --name="ospos-sql" -p 127.0.0.1:80:80 me/ospos


Depois de parar o recipiente criado pela primeira vez, este comando será substituído com

    docker run -d -v /app --volumes-from="ospos" -v /var/lib/mysql --volumes-from="ospos-sql" -p 127.0.0.1:80:80 me/ospos

Ambos os diretórios de dados MySQL e será mantido em um recipiente janela de encaixe separado e pode ser montado em qualquer outro recipiente utilizando o último comando. A guia de configuração mais extensa pode ser encontrada neste [site](http://www.opensourceposguide.com/guide/gettingstarted/installation)


