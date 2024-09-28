## Crypto Playground

[![Build Status](https://github.com/ikarolaborda/crypto-playground/actions/workflows/build.yml/badge.svg)](https://github.com/ikarolaborda/crypto-playground/actions)

Essa aplicação é parte do processo seletivo para desenvolvedor
backend para a empresa Wealth99, conforme os requisitos, foi escrita utilizando:
- PHP 7.4
- Laravel 5.6
- Docker
- Docker-compose
- Nginx

## Instalação

Primeiro, clone este repositório
```bash
git clone git@github.com:ikarolaborda/crypto-playground.git
```

Em seguida, execute o seguinte comando:

```bash
make build
# Se estiver no Windows, é necessario ter o Maketools instalado
```
Isso fará com que a aplicação fique disponível em

```
http://localhost
```

## Uso da API

para utilizar corretamente esta API, é necessário ter uma API Key 
da [CoinGecko](https://pro-api.coingecko.com/api/v3/)
Siga as instruções no website deles para obter a sua.

## Endpoints

| Método |      URI      |  Nome |
|--------|:-------------:|------:|
| GET    | api/v1/coin/current  | coin.current_price |
| GET    |   api/v1/coin/historical    |   coin.historical_price |
| GET    | api/v1/exchange-rates |    exchange.rates |

## Testes

Esta aplicação possui Testes de unidade e feature,
para correr os testes, podes utilizar:
```bash
make test
```
ou, em alternativa:
```bash
make lvtest
```

### Detalhes técnicos
Esta aplicação utiliza cache Redis para economia de recursos, ao evitar que várias chamadas 
À API da CoinGecko sejam feitas em um curto período de tempo (o que resultaria em HTTP 419)

