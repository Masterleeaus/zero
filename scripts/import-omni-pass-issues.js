#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const https = require('https');

// Parse command line arguments
const token = process.env.GITHUB_TOKEN;
const owner = 'masterleeaus';
const repo = 'zero';

if (!token) {
  console.error('Error: GITHUB_TOKEN environment variable is required');
  process.exit(1);
}

// Read all OMNI_PASS_*.md files
const docsDir = path.join(__dirname, '../docs/issues');
const files = fs.readdirSync(docsDir)
  .filter(f => f.startsWith('OMNI_PASS_') && f.endsWith('.md'))
  .sort();

console.log(`Found ${files.length} issue files to import`);

// Parse each file
const issues = files.map(file => {
  const content = fs.readFileSync(path.join(docsDir, file), 'utf-8');
  const lines = content.split('\n');

  // Extract title (first # header)
  const titleLine = lines.find(l => l.startsWith('# '));
  const title = titleLine ? titleLine.replace(/^# /, '') : file;

  // Extract labels and milestone
  let labels = [];
  let milestone = null;
  let bodyStart = 0;

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    if (line.startsWith('**Labels:**')) {
      const labelStr = line.replace('**Labels:**', '').trim().replace(/`/g, '');
      labels = labelStr.split(/[,\s]+/).filter(l => l.length > 0);
    }
    if (line.startsWith('**Milestone:**')) {
      milestone = line.replace('**Milestone:**', '').trim();
    }
    if (line.startsWith('---')) {
      bodyStart = i + 2;
      break;
    }
  }

  const body = lines.slice(bodyStart).join('\n').trim();

  return {
    title,
    body,
    labels,
    milestone,
    filename: file
  };
});

// Create issues via GitHub API
async function createIssue(issue) {
  return new Promise((resolve, reject) => {
    const payload = {
      title: issue.title,
      body: issue.body,
      labels: issue.labels
    };

    const data = JSON.stringify(payload);

    // Try local git server first, then fall back to github.com
    const isLocal = process.env.GIT_REMOTE_URL ? process.env.GIT_REMOTE_URL.includes('127.0.0.1') : false;

    let options;
    if (isLocal || process.env.USE_LOCAL_API === 'true') {
      // Use local git server API
      const http = require('http');
      options = {
        hostname: '127.0.0.1',
        port: 43293,
        path: `/api/v3/repos/${owner}/${repo}/issues`,
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Content-Length': data.length,
          'User-Agent': 'node-script',
          'Authorization': `token ${token}`,
          'Accept': 'application/vnd.github.v3+json'
        }
      };
      // Use http for local, https for remote
      const req = http.request(options, handleResponse);
      req.on('error', reject);
      req.write(data);
      req.end();
    } else {
      // Use GitHub.com API
      options = {
        hostname: 'api.github.com',
        port: 443,
        path: `/repos/${owner}/${repo}/issues`,
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Content-Length': data.length,
          'User-Agent': 'node-script',
          'Authorization': `token ${token}`,
          'Accept': 'application/vnd.github.v3+json'
        }
      };
      const req = https.request(options, handleResponse);
      req.on('error', reject);
      req.write(data);
      req.end();
    }

    function handleResponse(res) {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        if (res.statusCode >= 200 && res.statusCode < 300) {
          const result = JSON.parse(body);
          resolve(result);
        } else {
          reject(new Error(`API Error ${res.statusCode}: ${body}`));
        }
      });
    }
  });
}

// Import all issues
async function importAll() {
  console.log('\nImporting issues...\n');

  for (const issue of issues) {
    try {
      console.log(`Creating: ${issue.title}`);
      const result = await createIssue(issue);
      console.log(`  ✓ Created issue #${result.number}\n`);
    } catch (error) {
      console.error(`  ✗ Failed: ${error.message}\n`);
    }
  }

  console.log('Import complete!');
}

importAll().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
