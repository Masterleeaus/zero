# Log Sampling Management Runbook

## Overview
This runbook provides guidance on managing and troubleshooting the log sampling system in the Quantum Infrastructure Zero environment.

## Table of Contents
1. [Sampling Strategy](#sampling-strategy)
2. [Common Operations](#common-operations)
3. [Troubleshooting](#troubleshooting)
4. [Alert Response Guide](#alert-response-guide)
5. [Best Practices](#best-practices)

## Sampling Strategy

### Sampling Rules Hierarchy
1. **High-Priority Logs** (100% sampled)
   - Errors and critical events
   - Security-related logs
   - Authentication/authorization events

2. **Medium-Priority Logs** (10-50% sampled)
   - Application logs
   - API request logs
   - Business transactions

3. **Low-Priority Logs** (0.1-5% sampled)
   - Debug logs
   - Health checks
   - High-volume metrics

### Configuration Management
- **Location**: `deploy/monitoring/logging/log-sampling-rules`
- **Update Process**:
  ```bash
  # Edit the ConfigMap
  kubectl edit configmap log-sampling-rules -n monitoring
  
  # Or apply from file
  kubectl apply -f deploy/monitoring/logging/log-sampling-rules.yaml
  
  # Verify changes
  kubectl get configmap log-sampling-rules -n monitoring -o yaml
  ```

## Common Operations

### View Current Sampling Rates
```bash
# Get current sampling rules
kubectl get configmap log-sampling-rules -n monitoring -o yaml

# View current sampling metrics
kubectl port-forward svc/prometheus-operated 9090 -n monitoring &
open http://localhost:9090/graph?g0.expr=sum(rate(fluentbit_input_records_total[5m]))%20by%20(_sampling_rate)
```

### Adjust Sampling Rates Manually
1. **Temporary Adjustment**:
   ```bash
   # Scale down sampling temporarily
   kubectl scale --replicas=0 deployment/log-sampler -n monitoring
   
   # Make configuration changes
   kubectl edit configmap log-sampling-rules -n monitoring
   
   # Scale back up
   kubectl scale --replicas=1 deployment/log-sampler -n monitoring
   ```

2. **Permanent Adjustment**:
   - Update the ConfigMap directly
   - Commit changes to version control
   - Deploy using GitOps workflow

### Force Rule Reload
```bash
# Delete the sampler pod to force reload
kubectl delete pod -l app=log-sampler -n monitoring

# Or restart the deployment
kubectl rollout restart deployment/log-sampler -n monitoring
```

## Troubleshooting

### Common Issues

#### 1. High Log Volume
**Symptoms**:
- Increased storage usage
- High CPU/memory on log processors

**Resolution**:
```bash
# Identify high-volume log sources
kubectl exec -it -n monitoring prometheus-0 -- \
  curl -s 'http://localhost:9090/api/v1/query?query=topk(10,sum(rate(fluentbit_input_records_total[5m]))by(pod))' | jq

# Adjust sampling rates for high-volume sources
kubectl edit configmap log-sampling-rules -n monitoring
```

#### 2. Sampling Not Working
**Symptoms**:
- No logs being sampled
- Sampling rate not matching configuration

**Resolution**:
```bash
# Check sampler logs
kubectl logs -l app=log-sampler -n monitoring

# Verify rule application
kubectl exec -it -n monitoring prometheus-0 -- \
  curl -s 'http://localhost:9090/api/v1/query?query=log_sampling_rule_applied_total' | jq

# Check for configuration errors
kubectl describe configmap log-sampling-rules -n monitoring
```

#### 3. High Memory Usage
**Symptoms**:
- OOM kills
- High memory usage in monitoring

**Resolution**:
```bash
# Check memory usage
kubectl top pod -n monitoring

# Adjust memory limits
kubectl edit deployment/log-sampler -n monitoring
# Increase resources.requests.memory and resources.limits.memory
```

## Alert Response Guide

### Alert: HighLogDropRate
**Severity**: Critical

**Investigation**:
```bash
# Check drop rates
kubectl exec -it -n monitoring prometheus-0 -- \
  curl -s 'http://localhost:9090/api/v1/query?query=sum(rate(fluentbit_output_dropped_records_total[5m]))by(reason)' | jq

# Check queue sizes
kubectl exec -it -n monitoring prometheus-0 -- \
  curl -s 'http://localhost:9090/api/v1/query?query=fluentbit_output_queue_size' | jq
```

**Remediation**:
1. Increase sampling rates for critical logs
2. Scale up log processors
3. Check for log spikes

### Alert: SamplingRuleError
**Severity**: Warning

**Investigation**:
```bash
# Check error details
kubectl logs -l app=log-sampler -n monitoring --tail=100 | grep -i error

# Verify rule syntax
kubectl get configmap log-sampling-rules -n monitoring -o yaml | yq r - 'data.rules.yaml'
```

**Remediation**:
1. Fix rule syntax
2. Restart the sampler
3. Verify rule application

## Best Practices

### Configuration
- Use meaningful rule names and descriptions
- Document all sampling decisions
- Version control all configurations
- Test changes in staging first

### Monitoring
- Monitor drop rates and errors
- Track storage usage trends
- Set up alerts for anomalies
- Regularly review sampling effectiveness

### Maintenance
- Review sampling rates quarterly
- Archive old configurations
- Document all manual overrides
- Train team members on procedures

### Performance
- Keep rule sets small and efficient
- Use label selectors carefully
- Monitor memory usage
- Set appropriate timeouts

## Emergency Procedures

### Disable Sampling Completely
```bash
# Scale down the sampler
kubectl scale --replicas=0 deployment/log-sampler -n monitoring

# Update Fluent Bit to disable sampling
kubectl edit configmap fluent-bit-config -n monitoring
# Set @INCLUDE output-s3.conf to @INCLUDE output-s3-direct.conf

# Restart Fluent Bit
kubectl rollout restart daemonset/fluent-bit -n monitoring
```

### Rollback Procedure
```bash
# Revert to previous ConfigMap version
kubectl rollout undo configmap/log-sampling-rules -n monitoring

# Or apply from backup
kubectl apply -f backups/log-sampling-rules-backup.yaml -n monitoring

# Restart sampler
kubectl rollout restart deployment/log-sampler -n monitoring
```

## References
- [Log Sampling Architecture](https://internal-docs/architecture/log-sampling)
- [Troubleshooting Guide](https://internal-docs/troubleshooting/log-sampling)
- [Performance Tuning](https://internal-docs/performance/log-sampling)
