import { readdir, readFile } from 'node:fs/promises';
import path from 'node:path';

const targetDir = path.resolve('resources/css/pages');
const hardcodedColorPattern = /#[0-9a-fA-F]{3,8}\b|rgba?\(/g;
const allowList = [
  '#fff', // explicit white text is acceptable for destructive/success badges
];

async function getCssFiles(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  return entries
    .filter((entry) => entry.isFile() && entry.name.endsWith('.css'))
    .map((entry) => path.join(dir, entry.name));
}

function isAllowed(match) {
  return allowList.includes(match.toLowerCase());
}

const files = await getCssFiles(targetDir);
const violations = [];

for (const file of files) {
  const content = await readFile(file, 'utf8');
  const matches = [...content.matchAll(hardcodedColorPattern)];

  for (const match of matches) {
    if (!isAllowed(match[0])) {
      violations.push({ file, value: match[0] });
    }
  }
}

if (violations.length > 0) {
  console.error('Hardcoded colors found in page CSS. Use theme tokens instead:');
  for (const violation of violations) {
    console.error(`- ${path.relative(process.cwd(), violation.file)}: ${violation.value}`);
  }
  process.exit(1);
}

console.log('Theme color check passed.');
