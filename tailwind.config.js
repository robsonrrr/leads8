module.exports = {
  purge: [
    "./application/views/**/*.{mustache,php}",
    "./application/**/*.php",
    "./public/**/*.{html,js}"
  ],
  darkMode: false,
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#ef4444',
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
        },
        secondary: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
        }
      },
      backgroundImage: {
        'gradient-main': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'gradient-header': 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
        'gradient-button': 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)',
        'gradient-spinner': 'linear-gradient(135deg, rgba(102,126,234,0.8) 0%, rgba(118,75,162,0.8) 100%)',
      }
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
}