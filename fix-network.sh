#!/bin/bash

echo "=== Diagnóstico e Correção de Rede Docker ==="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para verificar se comando foi bem sucedido
check_command() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ $1${NC}"
        return 1
    fi
}

echo -e "\n${YELLOW}1. Verificando containers...${NC}"
docker-compose ps
check_command "Containers listados"

echo -e "\n${YELLOW}2. Verificando rede sistema_network...${NC}"
if docker network inspect sistema_network > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Rede sistema_network existe${NC}"
    docker network inspect sistema_network | grep -A 5 "Containers"
else
    echo -e "${RED}✗ Rede sistema_network não encontrada${NC}"
    echo "Criando rede..."
    docker network create sistema_network
fi

echo -e "\n${YELLOW}3. Verificando conectividade...${NC}"
echo "Testando ping do backend para mysql..."
if docker exec sistema_backend ping -c 2 mysql > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Backend consegue pingar mysql${NC}"
else
    echo -e "${RED}✗ Backend NÃO consegue pingar mysql${NC}"
    echo "Verificando configuração de rede..."
    
    # Verificar se os containers estão na mesma rede
    BACKEND_NETWORK=$(docker inspect sistema_backend --format='{{range $net,$v := .NetworkSettings.Networks}}{{$net}} {{end}}')
    MYSQL_NETWORK=$(docker inspect sistema_mysql --format='{{range $net,$v := .NetworkSettings.Networks}}{{$net}} {{end}}')
    
    echo "Backend está nas redes: $BACKEND_NETWORK"
    echo "MySQL está nas redes: $MYSQL_NETWORK"
fi

echo -e "\n${YELLOW}4. Testando porta MySQL...${NC}"
if docker exec sistema_backend nc -zv mysql 3306 2>&1; then
    echo -e "${GREEN}✓ Porta 3306 acessível${NC}"
else
    echo -e "${RED}✗ Porta 3306 não acessível${NC}"
fi

echo -e "\n${YELLOW}5. Verificando configuração do Laravel...${NC}"
echo "DB_HOST configurado:"
docker exec sistema_backend php -r "echo config('database.connections.mysql.host');" 2>/dev/null || echo "Não foi possível ler configuração"

echo -e "\n${YELLOW}6. Solução proposta:${NC}"
echo "Execute os seguintes comandos:"
echo "docker-compose down"
echo "docker-compose up -d"
echo "sleep 20"
echo "docker exec sistema_backend php artisan config:clear"
echo "docker exec sistema_backend php artisan migrate"

echo -e "\n${YELLOW}Deseja executar a solução agora? (s/n)${NC}"
read -r resposta

if [ "$resposta" = "s" ]; then
    echo "Executando solução..."
    docker-compose down
    sleep 5
    docker-compose up -d
    echo "Aguardando 20 segundos..."
    sleep 20
    docker exec sistema_backend php artisan config:clear
    docker exec sistema_backend php artisan config:cache
    docker exec sistema_backend php artisan migrate
fi