# Notes

> [!Warning]
> This setup has not been reviewed, nor set up for use in production; use it **ONLY** on your local machine.

# Setup

1. Create `.env` in this directory with the admin password. Use `echo "$(pwgen -cns 20 1)_$(pwgen -cns 3 1)"` to generate one, or look up OpenSearch password requirements, and create one of your preference. Your login will be _admin_.

```
OPENSEARCH_INITIAL_ADMIN_PASSWORD=
```

2. Comment out `fluentbit` container in `docker-compose.yaml`.
3. `docker compose up`.
4. Wait until the cluster is green (_Cluster health status changed from \[YELLOW] to \[GREEN]_ in logs).
5. Restore `fluentbit` in `docker-compose.yaml`.
6. `docker compose up`.
7. Navigate to http://localhost:5601/app/management/opensearch-dashboards/indexPatterns.
8. Create index pattern: `fuzzrake-*`, select `@timestamp` in the _Time field_.
9. Browse http://localhost:5601/app/data-explorer/discover.
