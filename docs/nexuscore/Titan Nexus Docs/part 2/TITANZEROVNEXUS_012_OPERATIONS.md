# TITANZEROVNEXUS: Operations Manual & Runbooks

**Version:** 5.0  
**Document:** 012  
**Status:** Complete Operations Guide

---

## EXECUTIVE SUMMARY

This manual covers daily operations, monitoring, troubleshooting, and runbooks for running TitanZero Nexus in production.

---

## I. PRE-PRODUCTION CHECKLIST

### **Week Before Launch**

```
☐ Database
  ├─ All tables created and indexes verified
  ├─ Replication tested to 3 regions
  ├─ Backup schedule verified (hourly)
  └─ Recovery tested (point-in-time restore works)

☐ Application
  ├─ All 5 Sentinels deployed and tested
  ├─ AI pipeline fully integrated
  ├─ All 5 modes operational
  └─ ProcessRecord lifecycle verified

☐ Infrastructure
  ├─ Load balancers configured
  ├─ CDN setup for static assets
  ├─ Cache layer (Redis) scaled to 50GB
  └─ Monitoring agents deployed

☐ Security
  ├─ SSL certificates deployed
  ├─ API tokens generated
  ├─ Rate limiters configured
  ├─ DDoS protection enabled
  └─ Firewall rules verified

☐ Monitoring
  ├─ CloudWatch/DataDog dashboards created
  ├─ Alert thresholds set
  ├─ PagerDuty integration tested
  └─ On-call schedule published

☐ Documentation
  ├─ Runbooks reviewed by team
  ├─ Escalation paths documented
  ├─ Customer comms prepared
  └─ Status page setup
```

---

## II. DAILY OPERATIONS CHECKLIST

### **Morning (8 AM)**

```
☐ Check overnight monitoring
  ├─ Review error rate (should be <0.1%)
  ├─ Review latency p95 (should be <100ms)
  ├─ Review cost tracking (vs budget)
  └─ Any alerts overnight? → Investigate

☐ Database health
  ├─ Replication lag (<100ms?)
  ├─ Backup completion (completed successfully?)
  ├─ Query performance (any slow queries?)
  └─ Disk usage (% full?)

☐ Application health
  ├─ API response times
  ├─ Worker queue depth (messages processing?)
  ├─ Cache hit rate (>95%?)
  └─ Any exceptions in error logs?

☐ Cost tracking
  ├─ LLM token usage (within budget?)
  ├─ Infrastructure costs (normal?)
  └─ Any cost anomalies? → Investigate
```

### **Afternoon (2 PM)**

```
☐ Operational issues
  ├─ Any customer reports? → Triage
  ├─ Any failed ProcessRecords? → Root cause
  ├─ Any permissions errors? → Check AEGIS
  └─ Any data inconsistencies? → Run validation
```

### **Evening (5 PM)**

```
☐ End-of-day checks
  ├─ Daily cost report (within budget?)
  ├─ Any escalations needed? (high-risk actions)
  ├─ Backup verification (today's backup good?)
  └─ Any on-call alerts for tomorrow?
```

---

## III. KEY METRICS TO MONITOR

### **Performance Metrics**

```
Metric: Query Latency (p50, p95, p99)
Target: <50ms, <100ms, <500ms
Alert: p95 > 200ms → Page on-call

Metric: Error Rate
Target: <0.1%
Alert: >1% → Automatic rollback trigger

Metric: ProcessRecord Success Rate
Target: >99.9%
Alert: <99% → Investigate

Metric: Signal Processing Lag
Target: <1 second
Alert: >5 seconds → Page on-call

Metric: Cache Hit Rate
Target: >95%
Alert: <80% → Page on-call
```

### **Cost Metrics**

```
Metric: Daily LLM Spend
Budget: $15.84 (for 50M queries/day)
Alert: >$25/day → Investigate anomaly

Metric: Infrastructure Cost
Budget: $1.3K/day (at 1M users)
Alert: >$2K/day → Investigate

Metric: Cost per Query
Target: $0.00032
Alert: >$0.001 → Pipeline inefficiency
```

### **Availability Metrics**

```
Metric: System Uptime
Target: 99.99%
Alert: <99.9% → Page on-call

Metric: Database Replication Lag
Target: <100ms
Alert: >1 second → Page on-call

Metric: Backup Completion
Target: 100% of scheduled backups
Alert: Any missed backup → Page on-call
```

---

## IV. ALERT ESCALATION MATRIX

```
Alert Level 1 (Info) - Auto-fix or monitor:
├─ Cache hit rate below 90% (→ increase cache)
├─ Query latency p50 slightly elevated (not p95)
└─ Signal processing taking 2-3 seconds (not 5+)

Alert Level 2 (Warning) - Page on-call within 1 hour:
├─ Error rate 0.5-1%
├─ Latency p95 >150ms
├─ Signal processing lag >5 seconds
├─ Replication lag >500ms
└─ Cost overage 20-50%

Alert Level 3 (Critical) - Page on-call immediately:
├─ Error rate >1%
├─ Latency p95 >300ms
├─ Replication completely stalled
├─ Multiple ProcessRecords in 'escalating' state
├─ Cost overage >50%
└─ Data consistency issues detected
```

---

## V. RUNBOOKS

### **RUNBOOK 1: Error Rate Spike**

**Symptom:** Error rate suddenly jumps to 1%+

**Investigation (10 min):**

```
1. Check error logs
   $ tail -f /var/log/nexus/error.log | grep -i error
   
2. Identify error pattern
   ├─ AI pipeline errors? (check LLM API logs)
   ├─ Database errors? (check replication lag)
   ├─ Permission errors? (check AEGIS logs)
   └─ Validation errors? (check ProcessRecord validation)

3. Check ProcessRecords with 'rejected' state
   SELECT COUNT(*), rejection_reason
   FROM process_records
   WHERE created_at > NOW() - INTERVAL 1 HOUR
   GROUP BY rejection_reason
   ORDER BY COUNT(*) DESC;
```

**Common Causes & Fixes:**

```
Cause 1: LLM API rate limit hit
├─ Check: Are we calling LLM too often?
├─ Fix: Increase cache TTL
│   $ php artisan nexus:config:set cache.logiccore_ttl 14400
├─ Verify: Monitor for 5 minutes
└─ If still high: Page on-call for API scaling

Cause 2: Database connectivity
├─ Check: SELECT 1; (does it hang?)
├─ Fix: Check replication lag
│   mysql> SHOW SLAVE STATUS\G
├─ Fix: Restart replication if needed
│   mysql> STOP SLAVE; START SLAVE;
└─ Verify: Lag should drop to <100ms

Cause 3: Validation rules too strict
├─ Check: Are we rejecting valid requests?
│   SELECT request_data, validation_errors
│   FROM process_records
│   WHERE state = 'rejected'
│   LIMIT 10;
├─ Fix: Review and adjust rules
│   $ php artisan nexus:config:edit approval_matrix
└─ Verify: Error rate drops

Cause 4: Permissions misconfigured
├─ Check: AEGIS logs for 'permission_denied'
├─ Fix: Verify user roles
│   SELECT user_id, mode, role FROM mode_access_control LIMIT 10;
├─ Fix: Grant missing permissions
│   $ php artisan nexus:grant-permission user_id mode_name role
└─ Verify: Error rate drops
```

**Escalation:**

```
If error rate >5%:
├─ Declare SEV-1 incident
├─ Page on-call immediately
├─ Consider partial rollback (disable AI pipeline)
│   $ php artisan nexus:disable-ai-pipeline
├─ Investigate root cause offline
└─ Re-enable when confident
```

---

### **RUNBOOK 2: Latency Degradation**

**Symptom:** Response times slow from 50ms to 200ms+

**Investigation (5 min):**

```
1. Check database performance
   mysql> SHOW PROCESSLIST;
   → Any long-running queries?
   
2. Check query execution plans
   mysql> EXPLAIN SELECT ... FROM entities WHERE ...;
   → Are we using indexes?
   
3. Check cache hit rate
   $ redis-cli INFO stats | grep hit_rate
   → Should be >95%

4. Check network latency
   $ ping -c 5 api.titanzero.io
   → Should be <50ms
```

**Common Causes & Fixes:**

```
Cause 1: Missing query index
├─ Symptom: Query takes 2+ seconds
├─ Fix: Add missing index
│   mysql> CREATE INDEX idx_company_status ON entities(company_id, status);
├─ Verify: EXPLAIN shows index usage
└─ Verify: Latency p95 drops below 100ms

Cause 2: Cache eviction
├─ Symptom: Cache hit rate drops to 60%
├─ Cause: High traffic causing cache churn
├─ Fix: Increase Redis memory
│   $ aws elasticache modify-cache-cluster --cache-cluster-id nexus-cache --cache-node-type cache.r6g.xlarge
├─ Wait: 5-10 minutes for restart
└─ Verify: Hit rate recovers to 95%

Cause 3: Database connection pool exhausted
├─ Symptom: Requests timeout waiting for connections
├─ Check: SELECT count(*) FROM INFORMATION_SCHEMA.PROCESSLIST;
├─ Fix: Increase pool size
│   config/database.php: 'connections' => 100
├─ Restart: $ php artisan horizon:terminate
└─ Verify: Latency improves

Cause 4: AI pipeline bottleneck
├─ Symptom: Latency increases during AI-heavy queries
├─ Check: Are we calling full 5-stage pipeline?
├─ Fix: Reduce conditional execution threshold
│   $ php artisan nexus:config:set pipeline.simple_query_threshold 0.7
├─ Verify: More queries skip early stages
└─ Verify: Latency p95 drops
```

---

### **RUNBOOK 3: Data Consistency Issue**

**Symptom:** Old and new schema don't match, or entities have conflicting attributes

**Investigation (15 min):**

```
1. Run consistency check
   $ php artisan nexus:validate:consistency --sample-size=1000
   
2. If mismatch found:
   └─ Identify specific entities with issues
   
3. Review audit trail for those entities
   SELECT * FROM audit_trail 
   WHERE entity_id = ? AND entity_type = ?
   ORDER BY timestamp DESC;
```

**Fix Procedures:**

```
Scenario 1: Entity has conflicting attributes
├─ Find conflicting row
│   SELECT * FROM entity_attributes 
│   WHERE entity_id = ? 
│   AND (attribute_name = 'title' OR attribute_name = 'status');
├─ Review to determine correct value
├─ Delete conflicting row
│   DELETE FROM entity_attributes WHERE id = ?;
├─ Verify: Re-run consistency check
└─ Root cause: Investigate how conflict occurred

Scenario 2: Relationship broken
├─ Identify broken relationship
│   SELECT * FROM entity_relationships 
│   WHERE from_entity_id = ? AND to_entity_id IS NULL;
├─ Recreate relationship
│   INSERT INTO entity_relationships (...) VALUES (...);
└─ Verify: Consistency check passes

Scenario 3: Mass data corruption
├─ If >1% of entities affected:
│   ├─ Stop all writes
│   ├─ Restore from last known-good backup
│   ├─ Replay transactions since backup
│   └─ Verify consistency
└─ If <1% affected:
    ├─ Fix individual entities manually
    └─ Monitor for recurrence
```

---

### **RUNBOOK 4: Cost Anomaly**

**Symptom:** Daily costs spike to 3x normal

**Investigation (5 min):**

```
1. Check LLM cost breakdown
   $ php artisan nexus:cost-report --breakdown=stage
   
2. Which stage caused spike?
   ├─ LogiCore (o3): Usually stable
   ├─ CreatiCore (Sonnet): Might be low cache hit
   ├─ OmegaCore (Opus): Might be new patterns
   ├─ OmicronCore (Command): Should scale with queries
   └─ EntropyCore (Haiku): Should be cheap
   
3. Check query volume
   SELECT COUNT(*) FROM tz_signals WHERE emitted_at > NOW() - INTERVAL 1 HOUR;
```

**Common Causes & Fixes:**

```
Cause 1: Cached learning degraded
├─ Symptom: CreatiCore being called 100% (normally 1%)
├─ Cause: Cache TTL expired or cache cleared
├─ Fix: Check Redis
│   $ redis-cli INFO keyspace
├─ Fix: Re-populate cache
│   $ php artisan nexus:warm-cache --stage=creative
└─ Verify: Calls drop back to 1%

Cause 2: Unexpected traffic spike
├─ Symptom: 50M queries/day → 500M queries/day
├─ Cause: Customer launched campaign or load test
├─ Fix: Contact customer to confirm planned
├─ Fix: Scale up infrastructure if needed
└─ If unauthorized spike:
    ├─ Check rate limiting
    ├─ Block if necessary
    └─ Investigate source

Cause 3: Broken caching
├─ Symptom: OmegaCore decision cache not working
├─ Check: Are we storing decisions?
│   SELECT COUNT(*) FROM omega_core_decisions;
├─ Fix: Check for Redis errors
│   $ redis-cli CLIENT LIST | grep -i error
├─ Fix: Restart Redis
│   $ systemctl restart redis-server
└─ Verify: Cache hits recover

Cause 4: AI model failure (expensive fallback)
├─ Symptom: Unexpected model called instead of cached
├─ Check: Are we hitting fallback logic?
│   grep -i "fallback\|retry" /var/log/nexus/*.log
├─ Fix: Check which model failed
├─ Fix: Investigate API errors
└─ Contact provider if API down
```

---

### **RUNBOOK 5: Database Replication Lag**

**Symptom:** Replication lag >1 second, customers see stale data

**Investigation (2 min):**

```
mysql> SHOW SLAVE STATUS\G
Look for: Seconds_Behind_Master

If >5 seconds:
├─ Check slave I/O thread
│   Slave_IO_Running: Should be "Yes"
├─ Check slave SQL thread
│   Slave_SQL_Running: Should be "Yes"
└─ Check for long-running queries
    mysql> SHOW PROCESSLIST;
```

**Fixes:**

```
If I/O thread is stopped:
├─ $ systemctl restart mysql
├─ $ mysql -u root -p
│   mysql> START SLAVE;
└─ Verify: Lag recovers to <100ms

If SQL thread is stopped:
├─ Most likely: Replication error
├─ Check error:
│   mysql> SHOW SLAVE STATUS\G | grep -i last_error;
├─ If constraint violation:
│   ├─ Skip erroneous transaction
│   │   SET GLOBAL SQL_SLAVE_SKIP_COUNTER = 1;
│   ├─ START SLAVE;
│   └─ Check for data corruption
├─ If other error: Page on-call
└─ Verify: Lag recovers

If master too busy:
├─ Check master processlist for long queries
├─ Kill non-critical queries
│   mysql> KILL <query_id>;
├─ Or wait for queries to complete
└─ Verify: Lag decreases
```

---

## VI. WEEKLY MAINTENANCE

### **Monday Morning**

```
☐ Review previous week's incidents
  ├─ Were they resolved?
  ├─ What was root cause?
  └─ How do we prevent recurrence?

☐ Cost review
  ├─ Was we under budget?
  ├─ Any unexpected spikes?
  └─ Forecast for coming week

☐ Capacity planning
  ├─ Query volume trending up?
  ├─ Need to scale anything?
  └─ Budget for new infrastructure?
```

### **Friday End-of-week**

```
☐ Backup validation
  ├─ Test restore from latest backup
  ├─ Verify all data present
  └─ Document procedure

☐ Security audit
  ├─ Review recent access logs
  ├─ Any suspicious activity?
  └─ Check permission matrix for oddities

☐ Performance analysis
  ├─ What was slowest query this week?
  ├─ Should we index anything?
  └─ Any opportunities for optimization?
```

---

## VII. MONTHLY MAINTENANCE

### **First Day of Month**

```
☐ Full backup test
  ├─ Restore to staging
  ├─ Verify data integrity
  ├─ Document restore time
  └─ Update RTO/RPO metrics

☐ Security review
  ├─ Audit all permission grants
  ├─ Review AEGIS logs for denials
  ├─ Check for orphaned accounts
  └─ Rotate API keys (if policy)

☐ Capacity planning
  ├─ Project growth for next 3 months
  ├─ Plan infrastructure scaling
  └─ Budget for new hardware

☐ Documentation update
  ├─ Update runbooks with recent issues
  ├─ Review architecture docs for accuracy
  └─ Update team training materials
```

---

## VIII. DISASTER RECOVERY SCENARIOS

### **Scenario 1: Primary Database Down**

```
Timeline: 0-5 minutes
├─ Automatic failover triggered
├─ Replica becomes new primary
└─ Connections reroute (DNS + route53)

If automatic failover fails:
├─ Page on-call immediately
├─ Manual failover procedure
│   ├─ Stop replication on replica
│   ├─ Promote replica to primary
│   ├─ Point all connections to new primary
│   ├─ Verify no data loss
│   └─ Restore old primary when ready
└─ Announce status to customers
```

### **Scenario 2: Region-wide Outage**

```
If AWS region down:
├─ Detect within 1 minute (health checks)
├─ Automatic failover to backup region
└─ Connections reroute via Route53 failover policy

Result:
├─ <5 second interruption
├─ Service restored in backup region
└─ Announce to customers

After recovery:
├─ Investigate cause
├─ Restore primary region when confident
└─ Failback traffic
```

### **Scenario 3: Data Corruption Detected**

```
If consistency check finds issues:
├─ Stop new writes (throttle to 1% traffic)
├─ Isolate issue to specific time range
├─ Restore from backup before corruption
├─ Replay clean transactions
└─ Verify integrity before resuming writes

If widespread corruption:
├─ Restore latest known-good backup (may lose hours of data)
├─ Notify affected customers
├─ Offer compensation if needed
└─ Conduct post-mortem
```

---

## CONCLUSION

Operations is about:
1. **Monitoring** - See problems before customers do
2. **Alerting** - Wake someone up at the right time
3. **Runbooks** - Know exactly what to do
4. **Testing** - Practice before it's an emergency

This manual provides the playbook for all of the above.

Keep it updated. Test procedures monthly. Train team annually.

