import { PageHeader, Form, Spin, Input, Typography, Button } from 'antd';
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
            if (response.data && response.data.status === 200) {
                if (response.data.payload && response.data.payload.telegramSettings) {
                    const telegramSettings = response.data.payload.telegramSettings;
                    
                    form.setFieldsValue({
                        TELEGRAM_BOT_TOKEN: telegramSettings.TELEGRAM_BOT_TOKEN || '',
                        TELEGRAM_CHAT_ID: telegramSettings.TELEGRAM_CHAT_ID || '',
                    });
                }
            }
        })
        .catch((error) => {
            Utils.handleException(error);
        })
        .finally(() => {
            setComponentLoading(false);
        });
    };

    const onFinish = (values) => {
        if (!loading) {
            setLoading(true);
        }

        HTTP.post(Routes.api.admin.telegramSettings, {
            TELEGRAM_BOT_TOKEN: values.TELEGRAM_BOT_TOKEN,
            TELEGRAM_CHAT_ID: values.TELEGRAM_CHAT_ID,
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
                                    required: false,
                                    message: 'Telegram Bot Token is required'
                                },
                            ]}
                        >
                            <Input placeholder="Enter Telegram Bot Token"/>
                        </Form.Item>
                        <Form.Item
                        name="TELEGRAM_CHAT_ID"
                        label="Telegram Chat ID"
                        rules={[
                            {
                                required: false,
                                message: 'Telegram Chat ID is required'
                            },
                        ]}
                    >
                        <Input placeholder="Enter Telegram Chat ID"/>
                    </Form.Item>
                    <Form.Item>
                        <Button type="primary" htmlType="submit" loading={loading}>
                            Save Settings
                        </Button>
                    </Form.Item>
                </Form>
            </Spin>
        </PageHeader>
    </React.Fragment>
    );
};

export default Telegram;