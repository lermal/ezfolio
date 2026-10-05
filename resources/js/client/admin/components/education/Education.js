import React, { useEffect, useState } from 'react';
import { Drawer, Button, Spin, Input, Form } from 'antd';
import styled from 'styled-components';
import PropTypes from 'prop-types';
import HTTP from '../../../common/helpers/HTTP';
import Utils from '../../../common/helpers/Utils';
import Routes from '../../../common/helpers/Routes';
import { CONTENT_LOCALES, LocaleTabs, showLocaleError, textPair } from '../locale/contentLocale';

const StyledDrawer = styled(Drawer)`
    .ant-drawer-content-wrapper {
        width: 520px !important;
        @media (max-width: 768px) {
            max-width: calc(100vw - 16px) !important;
        }
    }
`;

const Education = (props) => {
    const [visible, setVisible] = useState(false);
    const [form] = Form.useForm();
    const [loading, setLoading] = useState((typeof props.loading !== 'undefined') ? props.loading : false);
    const [componentLoading, setComponentLoading] = useState((typeof props.componentLoading !== 'undefined') ? props.componentLoading : false);
    const [locale, setLocale] = useState('ru');

    useEffect(() => {
        form.setFieldsValue({
            id: props.itemToEdit ? props.itemToEdit.id : '', 
            institution: textPair(props.itemToEdit ? props.itemToEdit.institution : ''), 
            period: textPair(props.itemToEdit ? props.itemToEdit.period : ''),
            degree: textPair(props.itemToEdit ? props.itemToEdit.degree : ''),
            cgpa: props.itemToEdit ? props.itemToEdit.cgpa : '',
            department: textPair(props.itemToEdit ? props.itemToEdit.department : ''),
            thesis: textPair(props.itemToEdit ? props.itemToEdit.thesis : ''),
        });
    }, [props.itemToEdit])

    useEffect(() => {
        setTimeout(() => {
            setVisible(props.visible);
        }, 100);
    }, [props.visible])

    useEffect(() => {
        if (typeof props.loading !== 'undefined') {
            setLoading(props.loading)
        }
    }, [props.loading])

    useEffect(() => {
        if (typeof props.componentLoading !== 'undefined') {
            setComponentLoading(props.componentLoading)
        }
    }, [props.componentLoading])

    const handleClose = () => {
        setVisible(false);
        setTimeout(() => {
            props.handleCancel();
        }, 400);
    };

    const handleOk = () => {
        form
        .validateFields()
        .then((values) => {
            
            //save form
            setLoading(true);

            HTTP[values.id ? 'put' : 'post'](Routes.api.admin.education+(values.id ? `/${values.id}` : '' ), {
                id: values.id,
                institution: values.institution,
                cgpa: values.cgpa,
                degree: values.degree,
                department: values.department,
                period: values.period,
                thesis: values.thesis,
            })
            .then(response => {
                Utils.handleSuccessResponse(response, () => {
                    form.resetFields();
                    Utils.showNotification(response.data.message, 'success');
                    props.submitCallback();
                })
            })
            .catch((error) => {
                Utils.handleException(error);
            }).finally(() => {
                setLoading(false);
            });
        })
        .catch((info) => {
            showLocaleError(info, setLocale);
            console.log('Validate Failed:', info);
        });
    }

    return (
        <StyledDrawer
            title={props.title}
            onClose={handleClose}
            visible={visible}
            destroyOnClose={true}
            maskClosable={false}
            forceRender={true}
            footer={
                <div
                    style={{
                        textAlign: 'right',
                    }}
                >
                    <Button disabled={componentLoading} onClick={handleClose} style={{ marginRight: 8 }}>
                        Cancel
                    </Button>
                    <Button disabled={componentLoading} onClick={handleOk} type="primary" loading={loading}>
                        Save
                    </Button>
                </div>
            }
        >
            <Spin spinning={componentLoading} size="large" delay={500}>
                <Form
                    preserve={false}
                    form={form}
                    layout="vertical"
                    name="education"
                >
                    <Form.Item name="id" hidden>
                        <Input/>
                    </Form.Item>
                    <LocaleTabs locale={locale} onChange={setLocale} />
                    {CONTENT_LOCALES.map((code) => (
                        <div key={code} style={{ display: locale === code ? 'block' : 'none' }}>
                            <Form.Item
                                name={['institution', code]}
                                label="Institution"
                                rules={code === 'ru' ? [{ required: true, message: 'Please input the name of institution' }] : []}
                            >
                                <Input placeholder="Enter Institution"/>
                            </Form.Item>
                            <Form.Item name={['period', code]} label="Period">
                                <Input placeholder="Enter Period"/>
                            </Form.Item>
                            <Form.Item name={['degree', code]} label="Degree">
                                <Input placeholder="Enter Degree"/>
                            </Form.Item>
                            <Form.Item name={['department', code]} label="Department">
                                <Input placeholder="Enter Department"/>
                            </Form.Item>
                            <Form.Item name={['thesis', code]} label="Thesis">
                                <Input placeholder="Enter Thesis"/>
                            </Form.Item>
                        </div>
                    ))}
                    <Form.Item name="cgpa" label="CGPA">
                        <Input placeholder="Enter CGPA"/>
                    </Form.Item>
                </Form>
            </Spin>
        </StyledDrawer>
    )
}

Education.propTypes = {
    handleCancel: PropTypes.func.isRequired,
    submitCallback: PropTypes.func.isRequired,
    visible: PropTypes.bool.isRequired,
    itemToEdit: PropTypes.object,
    loading: PropTypes.bool,
    componentLoading: PropTypes.bool,
    title: PropTypes.node,
}

export default Education;