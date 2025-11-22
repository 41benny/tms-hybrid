/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './app/View/**/*.php',
  ],
  theme: {
    extend: {},
  },
  // Production optimizations
  safelist: [
    // Keep commonly used utility classes
    'bg-green-100',
    'bg-blue-100',
    'bg-yellow-100',
    'bg-red-100',
    'text-green-600',
    'text-blue-600',
    'text-yellow-600',
    'text-red-600',
  ],
};

