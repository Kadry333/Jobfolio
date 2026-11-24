import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function resolveJobUrl(url: string | null | undefined): {
    href: string;
    isWhatsApp: boolean;
    displayLabel: string;
} {
    if (!url) {
        return { href: '', isWhatsApp: false, displayLabel: '' };
    }

    const trimmed = url.trim();
    if (!trimmed) {
        return { href: '', isWhatsApp: false, displayLabel: '' };
    }

    // 1. WhatsApp link formats (wa.me, api.whatsapp.com, whatsapp://)
    if (/^(https?:\/\/)?(wa\.me|api\.whatsapp\.com)/i.test(trimmed)) {
        const href = /^https?:\/\//i.test(trimmed)
            ? trimmed
            : `https://${trimmed}`;
        return { href, isWhatsApp: true, displayLabel: 'WhatsApp' };
    }

    if (trimmed.startsWith('whatsapp://')) {
        return { href: trimmed, isWhatsApp: true, displayLabel: 'WhatsApp' };
    }

    // 2. Phone / WhatsApp number (e.g. +2010..., 010..., +1...)
    const digitsOnly = trimmed.replace(/\D/g, '');
    const isDomainUrl =
        /^[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/.test(trimmed) &&
        !/^[+0-9\s().-]+$/.test(trimmed);

    if (!isDomainUrl && digitsOnly.length >= 6) {
        let waNumber = digitsOnly;
        if (trimmed.startsWith('+')) {
            waNumber = digitsOnly;
        } else if (trimmed.startsWith('00')) {
            waNumber = digitsOnly.substring(2);
        } else if (waNumber.startsWith('01') && waNumber.length === 11) {
            // Egyptian local format (010/011/012/015) -> prepend 2
            waNumber = '2' + waNumber;
        }
        return {
            href: `https://wa.me/${waNumber}`,
            isWhatsApp: true,
            displayLabel: 'WhatsApp',
        };
    }

    // 3. Regular website URL
    const href = /^https?:\/\//i.test(trimmed) ? trimmed : `https://${trimmed}`;
    return {
        href,
        isWhatsApp: false,
        displayLabel: 'Job Posting',
    };
}
