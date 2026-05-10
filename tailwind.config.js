/** @type {import('tailwindcss').Config} */
export default {
  // Tailwind dark mode dùng class="dark" trên <html>
  darkMode: 'class',

  // Scan tất cả PHP views để tree-shake CSS không dùng
  content: [
    './app/views/**/*.php',
    './index.php',
  ],

  theme: {
    extend: {
      // ─── Brand Colors (map từ CSS variables cũ) ────────────────
      colors: {
        primary: {
          DEFAULT: '#6366f1',
          dark:    '#4f46e5',
          light:   '#e0e7ff',
        },
        secondary: '#8b5cf6',
        accent:    '#ec4899',
        success:   '#10b981',
        warning:   '#f59e0b',
        danger:    '#ef4444',
        info:      '#06b6d4',

        // Dark mode surfaces
        dark: {
          bg:     '#0f172a',
          card:   '#1e293b',
          '2':    '#334155',
          '3':    '#475569',
          muted:  '#94a3b8',
          border: '#334155',
          text:   '#f8fafc',
        },

        // Light mode surfaces
        light: {
          bg:   '#f8fafc',
          card: '#ffffff',
          text: '#0f172a',
          muted: '#64748b',
          border: '#e2e8f0',
        },
      },

      // ─── Border Radius (map từ --radius-* variables) ───────────
      borderRadius: {
        sm:      '8px',
        DEFAULT: '14px',
        lg:      '20px',
        xl:      '24px',
      },

      // ─── Font ───────────────────────────────────────────────────
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
      },

      // ─── Box Shadows ────────────────────────────────────────────
      boxShadow: {
        'xs':         '0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04)',
        'sm-p':       '0 4px 16px rgba(99,102,241,.08)',
        'md-p':       '0 8px 32px rgba(99,102,241,.14)',
        'lg-p':       '0 20px 60px rgba(99,102,241,.18)',
        'glow':       '0 0 40px rgba(99,102,241,.35)',
        'dark-sm':    '0 4px 16px rgba(0,0,0,.4)',
        'dark-md':    '0 8px 32px rgba(0,0,0,.6)',
        'dark-lg':    '0 20px 60px rgba(0,0,0,.8)',
      },

      // ─── Transitions ────────────────────────────────────────────
      transitionTimingFunction: {
        'spring':  'cubic-bezier(.34,1.56,.64,1)',
        'smooth':  'cubic-bezier(.4,0,.2,1)',
      },
      transitionDuration: {
        '220': '220ms',
        '350': '350ms',
      },

      // ─── Gradient Colors (dùng với bg-gradient-to-*) ───────────
      backgroundImage: {
        'grad-primary': 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%)',
        'grad-dark':    'linear-gradient(180deg, #1e293b 0%, #0f172a 100%)',
        'grad-hero':    'linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4f46e5 100%)',
        'grad-success': 'linear-gradient(135deg, #059669, #10b981)',
        'grad-danger':  'linear-gradient(135deg, #dc2626, #ef4444)',
      },

      // ─── Custom Animations ──────────────────────────────────────
      keyframes: {
        'float-orb': {
          'from': { transform: 'translate(0,0) scale(1)' },
          'to':   { transform: 'translate(30px,20px) scale(1.08)' },
        },
        'slide-up': {
          'from': { opacity: '0', transform: 'translateY(40px) scale(.97)' },
          'to':   { opacity: '1', transform: 'translateY(0) scale(1)' },
        },
        'shimmer': {
          '0%':   { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        'badge-pulse': {
          '0%, 100%': { boxShadow: '0 0 0 0 rgba(239,68,68,.4)' },
          '50%':       { boxShadow: '0 0 0 6px rgba(239,68,68,0)' },
        },
        'price-pulse': {
          '0%, 100%': { opacity: '1' },
          '50%':       { opacity: '.7' },
        },
      },
      animation: {
        'float-orb':   'float-orb 8s ease-in-out infinite alternate',
        'slide-up':    'slide-up .5s cubic-bezier(.16,1,.3,1)',
        'shimmer':     'shimmer 1.4s infinite',
        'badge-pulse': 'badge-pulse 1.5s ease-in-out infinite',
        'price-pulse': 'price-pulse 2s ease-in-out infinite',
      },
    },
  },

  plugins: [],
};
