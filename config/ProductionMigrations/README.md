# Production migrations for the existing CakePHP 2 database

This directory intentionally contains only the reviewed, additive CRM list and pagination indexes.
Do not replace its contents with the complete historical `config/Migrations` directory.

From the project root, preview the SQL first:

```powershell
php bin\cake.php migrations migrate --source ..\ProductionMigrations --dry-run --no-lock
```

The preview must contain only index additions. After taking a verified database backup, run during a maintenance window:

```powershell
php bin\cake.php migrations migrate --source ..\ProductionMigrations --no-lock
```

Large MyISAM Customer/Contact tables can remain locked while indexes are built. Do not interrupt an active index build.

Never run the unscoped command below against the legacy production database:

```powershell
php bin\cake.php migrations migrate
```
