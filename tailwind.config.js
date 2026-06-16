/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './app/Views/**/*.php',
    './public/**/*.html',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0369a1',      // Azul profesional
        'primary-light': '#06b6d4', // Azul claro
        'primary-dark': '#164e63', // Azul oscuro
        secondary: '#64748b',    // Gris
        accent: '#0ea5e9',       // Azul cielo
        background: '#f8fafc',   // Gris muy claro (fondo)
        text: {
          primary: '#0f172a',    // Negro muy oscuro
          secondary: '#475569',  // Gris oscuro
          muted: '#94a3b8',      // Gris medio
        },
      },
      fontFamily: {
        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
        serif: ['Merriweather', 'serif'],
      },
      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem' }],
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem', { lineHeight: '1.5rem' }],
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
      },
      spacing: {
        'safe-top': 'env(safe-area-inset-top)',
      },
      boxShadow: {
        'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
      },
    },
  },
  plugins: [],
}
