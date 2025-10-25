import { PageHeader, Form, Spin, Input, Select, Button, Typography } from 'antd';
import React, { useEffect, useState } from 'react';
import { InfoCircleOutlined } from '@ant-design/icons';
import HTTP from '../../../common/helpers/HTTP';
import Routes from '../../../common/helpers/Routes';
import Utils from '../../../common/helpers/Utils';

const { Option } = Select;

const Turnstile = () => {
    const [loading, setLoading] = useState(false);
    const [componentLoading, setComponentLoading] = useState(false);
    const [form] = Form.useForm();

    useEffect(() => {
        loadTurnstileSetting();
    }, [])

    const loadTurnstileSetting = (_componentLoading = true) => {
        setComponentLoading(_componentLoading);

        HTTP.get(Routes.api.admin.settings)
        .then(response => {
            Utils.handleSuccessResponse(response, () => {
                form.setFieldsValue({
                    TURNSTILE_SITE_KEY: response.data.payload.turnstileSettings.TURNSTILE_SITE_KEY,
                    TURNSTILE_SECRET_KEY: response.data.payload.turnstileSettings.TURNSTILE_SECRET_KEY,
                });
            })
        })
        .catch((error) => {
            Utils.handleException(error);
        }).finally(() => {
            setComponentLoading(false);
        });
    }

    const onFinish = (values) => {
        if (!loading) {
            setLoading(true);
        }

        HTTP.post(Routes.api.admin.mailSettings, {
            TURNSTILE_SITE_KEY: values.TURNSTILE_SITE_KEY,
            TURNSTILE_SECRET_KEY: values.TURNSTILE_SECRET_KEY,
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
                title="Turnstile Settings"
                subTitle={
                    <Typography.Text
                        style={{ width: '100%', color: 'grey' }}
                        ellipsis={{ tooltip: 'Optional and needed for turnstile verification' }}
                    >
                        Optional and needed for turnstile verification
                    </Typography.Text>
                }
            >
                <Spin spinning={componentLoading} delay={500} size="large">
                    <Form
                        layout="vertical"
                        name="turnstile-setting"
                        form={form}
                        onFinish={onFinish}
                        onFinishFailed={onFinishFailed}
                    >
                        <Form.Item
                            name="TURNSTILE_SITE_KEY"
                            label="Turnstile Site Key"
                            rules={[
                                {
                                    required: true,
                                    message: 'Turnstile Site Key is required'
                                },
                            ]}
                        >
                            <Input placeholder="Enter Turnstile Site Key"/>
                        </Form.Item>
                        <Form.Item
                            name="TURNSTILE_SECRET_KEY"
                            label="Turnstile Secret Key"
                            rules={[
                                {
                                    required: true,
                                    message: 'Turnstile Secret Key is required'
                                },
                            ]}
                        >
                            <Input placeholder="Enter Turnstile Secret Key"/>
                        </Form.Item>
                        <Form.Item 
                            style={{
                                textAlign: 'right',
                            }}
                        >
                            <Button loading={loading} type="primary" htmlType="submit">
                                Save Settings
                            </Button>
                        </Form.Item>
                    </Form>
                </Spin>
            </PageHeader>
        </React.Fragment>
    );
};

export default Turnstile;