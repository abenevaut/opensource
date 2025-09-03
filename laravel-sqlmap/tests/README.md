- https://sqlmap.org/
- https://edricteo.com/sqlmap-commands/
- https://medium.com/@cuncis/the-ultimate-sqlmap-tutorial-master-sql-injection-and-vulnerability-assessment-4babdc978e7d
- https://medium.com/@tushar_rs_/sqlmap-a-comprehensive-guide-to-sql-injection-testing-37220e77b0ee

```
python /d/projects/sqlmap/sqlmap.py
    -r "sqlmap.http"
    --batch
    --level=5 
    --risk=3
    --flush-session 
    --fresh-queries
```

```
    --dbms="Microsoft SQL Server %sqlmapSqlServerYearVersion%"
```
