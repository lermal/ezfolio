import { PageHeader, Form, Spin, Input, Button, Typography } from 'antd';
import React, { useEffect, useState } from 'react';
import HTTP from '../../../common/helpers/HTTP';
import Routes from '../../../common/helpers/Routes';
import Utils from '../../../common/helpers/Utils';

const Telegram = () => {
    const [loading, setLoading] = useState(false);
    const [componentLoading, setComponentLoading] = useState(false);
    const [form] = Form.useForm();

    useEffect(() => {
        loadTelegramSetting();
    }, [])

    const loadTelegramSetting = (_componentLoading = true) => {
        setComponentLoading(_componentLoading);

        HTTP.get(Routes.api.admin.settings)
        .then(response => {
            Utils.handleSuccessResponse(response, () => {
                if (response.data.payload && response.data.payload.telegramSettings) {
                    form.setFieldsValue({
                        TELEGRAM_BOT_TOKEN: response.data.payload.telegramSettings.TELEGRAM_BOT_TOKEN || '',
                        TELEGRAM_CHAT_IDS: response.data.payload.telegramSettings.TELEGRAM_CHAT_IDS.split(',') || [],
                    });
                }
            });
        });
    };

    const onFinish = (values) => {
        if (!loading) {
            setLoading(true);
        }

        HTTP.post(Routes.api.admin.telegramSettings, {
            TELEGRAM_BOT_TOKEN: values.TELEGRAM_BOT_TOKEN,
            TELEGRAM_CHAT_IDS: values.TELEGRAM_CHAT_IDS,
        })
        .then(response => {
            Utils.handleSuccessResponse(response, () => {
                Utils.showNotification(response.data.message, 'success');
            })
        })
        .catch((error) => {
            Utils.handleException(error);
        }).finally(() => {
            setLoading(false);
        });
    };

    const onFinishFailed = errorInfo => {
        console.log('Failed:', errorInfo);
    };

    return (
        <React.Fragment>
            <PageHeader
                title="Telegram Settings"
                subTitle={
                    <Typography.Text
                        style={{ width: '100%', color: 'grey' }}
                        ellipsis={{ tooltip: 'Optional and needed for telegram bot' }}
                    >
                        Optional and needed for telegram bot
                    </Typography.Text>
                }
            >
                <Spin spinning={componentLoading} delay={500} size="large">
                    <Form
                        layout="vertical"
                        name="telegram-setting"
                        form={form}
                        onFinish={onFinish}
                        onFinishFailed={onFinishFailed}
                    >
                        <Form.Item
                            name="TELEGRAM_BOT_TOKEN"
                            label="Telegram Bot Token"
                            rules={[
                                {
                                    required: true,
                                    message: 'Telegram Bot Token is required'
                                },
                            ]}
                        >
                            <Input placeholder="Enter Telegram Bot Token"/>
                        </Form.Item>
                        <Form.Item
                        name="TELEGRAM_CHAT_IDS"
                        label="Telegram Chat IDs"
                        rules={[
                            {
                                required: true,
                                message: 'Telegram Chat IDs is required'
                            },
                        ]}
                    >
                        <Select
                            mode="tags"
                            allowClear
                            placeholder="Enter Telegram Chat IDs"
                        >
                            {response.data.payload.telegramSettings.TELEGRAM_CHAT_IDS.map((chatId, index) => (
                                <Select.Option key={index} value={chatId}>{chatId}</Select.Option>
                            ))}
                        </Select>
                    </Form.Item>
                </Form>
            </Spin>
        </PageHeader>
    </React.Fragment>
    );
};

export default Telegram;