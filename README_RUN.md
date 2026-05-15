# Como rodar o projeto

Use Docker para este projeto. O `.env` do Laravel usa `DB_HOST=mysql`, que funciona dentro da rede Docker.

```bash
chmod +x start-project.sh fix-network.sh
./start-project.sh
```

Se o banco ficou quebrado por uma tentativa anterior de migration, resete o banco de desenvolvimento:

```bash
./start-project.sh --fresh
```

Depois de subir:

- Frontend: `http://localhost:3000`
- API Laravel: `http://localhost:8000/api`

Comandos úteis:

```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec backend php artisan optimize:clear
docker compose logs -f backend
docker compose logs -f mysql
```

Se você quiser rodar `php artisan migrate` direto no host, troque temporariamente o banco para:

```env
DB_HOST=127.0.0.1
DB_PORT=3307
```

Para Docker, deixe:

```env
DB_HOST=mysql
DB_PORT=3306
```
