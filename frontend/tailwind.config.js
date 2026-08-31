/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        serif: ['Playfair Display', 'serif'],
        brand: ['Playfair Display', 'serif'],
      },
      colors: {
        navy: '#1A2A3A',
        'navy-hover': '#23374D',
        'navy-dark': '#131F2B',
        'navy-light': '#2B3E52',
        gold: '#C9A96E',
        'gold-hover': '#B8975B',
        'gold-light': '#DFCB9B',
        stone: '#E8E4DE',
        'stone-light': '#F3F0EC',
        'stone-dark': '#D0C9C0',
        ivory: '#F8F6F2',
        'ivory-light': '#FCFBF9',
        primary: {
          50: '#f0f4f8',
          100: '#d9e2ec',
          200: '#bcccdc',
          300: '#9fb3c8',
          400: '#829ab1',
          500: '#627d98',
          600: '#1A2A3A',
          700: '#131F2B',
          800: '#0E1720',
          900: '#090F15',
        },
      },
      boxShadow: {
        card: '0 2px 12px rgba(0,0,0,0.06)',
        'gold-glow': '0 0 0 3px rgba(201, 169, 110, 0.25)',
      },
    },
  },
  plugins: [],
}
