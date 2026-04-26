tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Space Grotesk', 'sans-serif'],
                display: ['Source Serif 4', 'serif']
            },
            colors: {
                primary: '#D9480F',
                secondary: '#6B4F3B',
                success: '#2F855A',
                danger: '#C53030',
                ink: '#211C1A',
                sand: '#F4ECE3',
                clay: '#EAD7C3',
                ember: '#FFEDD5',
                gold: '#F6AD55'
            },
            boxShadow: {
                panel: '0 24px 80px -32px rgba(33, 28, 26, 0.28)'
            },
            backgroundImage: {
                mesh: 'radial-gradient(circle at top left, rgba(244, 114, 34, 0.18), transparent 32%), radial-gradient(circle at bottom right, rgba(120, 53, 15, 0.16), transparent 28%)'
            },
            keyframes: {
                rise: {
                    '0%': { opacity: '0', transform: 'translateY(18px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' }
                }
            },
            animation: {
                rise: 'rise 0.6s ease-out both'
            }
        }
    }
};
