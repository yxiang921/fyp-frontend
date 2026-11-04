# Demo accounts

These accounts are for local demonstration and testing only. All use the password `FindSightDemo!2026`. Change or remove them before deploying the system publicly.

| Role | Email | Patrol area |
|---|---|---|
| Super admin | `demo.admin@findsight.test` | All areas (administrative access) |
| PDRM officer | `demo.officer@findsight.test` | All areas (officer access) |
| Patrol unit | `demo.patrol.skudai@findsight.test` | Skudai |
| Patrol unit | `demo.patrol.jb@findsight.test` | Johor Bahru |
| Patrol unit | `demo.patrol.kl@findsight.test` | Kuala Lumpur Central |
| Public reporter | `demo.public@findsight.test` | Not applicable |

Portal login: `http://127.0.0.1:8000/login`

The seeder is idempotent and may be run again with:

```powershell
php artisan db:seed --class=DemoAccountSeeder
```
