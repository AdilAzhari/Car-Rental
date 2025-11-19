import { createI18n } from 'vue-i18n'

// Import translation files
import en from './lang/en.json'
import ar from './lang/ar.json'

// Get the current locale from the page props or fallback to 'en'
const getLocale = () => {
    // Check if we have page props available (from Inertia)
    const pageElement = document.getElementById('app')
    if (pageElement && pageElement.dataset.page) {
        try {
            const pageProps = JSON.parse(pageElement.dataset.page)
            return pageProps.props.locale || 'en'
        } catch (e) {
            console.warn('Failed to parse page props for locale')
        }
    }

    // Check localStorage for previously saved locale
    const savedLocale = localStorage.getItem('locale')
    if (savedLocale && ['en', 'ar'].includes(savedLocale)) {
        return savedLocale
    }

    // Fallback to checking Laravel's locale meta tag
    const metaLocale = document.querySelector('meta[name="locale"]')
    if (metaLocale) {
        return metaLocale.getAttribute('content') || 'en'
    }

    // Ultimate fallback
    return 'en'
}

// Create i18n instance
const i18n = createI18n({
    legacy: false, // Use Composition API mode
    locale: getLocale(),
    fallbackLocale: 'en',
    messages: {
        en,
        ar
    },
    // Enable number and date formatting
    numberFormats: {
        en: {
            currency: {
                style: 'currency',
                currency: 'MYR',
                currencyDisplay: 'symbol'
            },
            decimal: {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        },
        ar: {
            currency: {
                style: 'currency',
                currency: 'MYR',
                currencyDisplay: 'symbol'
            },
            decimal: {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        }
    },
    datetimeFormats: {
        en: {
            short: {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            },
            long: {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long',
                hour: 'numeric',
                minute: 'numeric'
            }
        },
        ar: {
            short: {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            },
            long: {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long',
                hour: 'numeric',
                minute: 'numeric'
            }
        }
    }
})

export default i18n