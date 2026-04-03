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

    const options = {
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

    const req = https.request(options, (res) => {
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
    });

    req.on('error', reject);
    req.write(data);
    req.end();
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
