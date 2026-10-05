import { Radio } from 'antd';
import PropTypes from 'prop-types';
import React from 'react';

export const CONTENT_LOCALES = ['ru', 'en'];

export const LocaleTabs = ({ locale, onChange }) => (
    <Radio.Group
        value={locale}
        onChange={(event) => onChange(event.target.value)}
        style={{ marginBottom: 16 }}
        role="tablist"
        aria-label="Language"
    >
        {CONTENT_LOCALES.map((code) => (
            <Radio.Button key={code} value={code} role="tab" aria-selected={locale === code}>
                {code.toUpperCase()}
            </Radio.Button>
        ))}
    </Radio.Group>
);

LocaleTabs.propTypes = {
    locale: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
};

export const localeText = (value) => {
    if (value && typeof value === 'object' && !Array.isArray(value)) {
        return value.ru || value.en || '';
    }

    return value == null ? '' : String(value);
};

export const textPair = (value) => {
    if (value && typeof value === 'object' && !Array.isArray(value) && ('ru' in value || 'en' in value)) {
        return {
            ru: value.ru || '',
            en: value.en || '',
        };
    }

    return {
        ru: value || '',
        en: '',
    };
};

export const listPair = (value) => {
    if (Array.isArray(value)) {
        return { ru: value, en: [] };
    }

    if (typeof value === 'string') {
        if (!value) {
            return { ru: [], en: [] };
        }

        try {
            return listPair(JSON.parse(value));
        } catch (error) {
            return { ru: [], en: [] };
        }
    }

    if (value && typeof value === 'object') {
        return {
            ru: Array.isArray(value.ru) ? value.ru : [],
            en: Array.isArray(value.en) ? value.en : [],
        };
    }

    return { ru: [], en: [] };
};

export const showLocaleError = (info, setLocale) => {
    const fields = info && info.errorFields ? info.errorFields : [];
    const names = fields.map((field) => (Array.isArray(field.name) ? field.name : []));
    const english = names.some((name) => name.indexOf('en') !== -1);
    const russian = names.some((name) => name.indexOf('ru') !== -1);

    if (russian) {
        setLocale('ru');
    } else if (english) {
        setLocale('en');
    }
};
