import React, { useEffect, useState } from 'react';
import { Drawer, Button, Spin, Input, Form } from 'antd';
import styled from 'styled-components';
import PropTypes from 'prop-types';
import HTTP from '../../../common/helpers/HTTP';
import Utils from '../../../common/helpers/Utils';
import Routes from '../../../common/helpers/Routes';

const StyledDrawer = styled(Drawer)`
    .ant-drawer-content-wrapper {
        width: 520px !important;
        @media (max-width: 768px) {
            max-width: calc(100vw - 16px) !important;
        }
    }
`;

const Service = (props) => {
    const [visible, setVisible] = useState(false);
    const [form] = Form.useForm();
    const [loading, setLoading] = useState((typeof props.loading !== 'undefined') ? props.loading : false);
    const [componentLoading, setComponentLoading] = useState((typeof props.componentLoading !== 'undefined') ? props.componentLoading : false);

    useEffect(() => {
        form.setFieldsValue({
            id: props.itemToEdit ? props.itemToEdit.id : '', 
            title: props.itemToEdit ? props.itemToEdit.title : '', 
            slug: props.itemToEdit ? props.itemToEdit.slug : '',
            icon: props.itemToEdit ? props.itemToEdit.icon : '',
            details: props.itemToEdit ? props.itemToEdit.details : '',
            content: props.itemToEdit ? props.itemToEdit.content : '',
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

            HTTP[values.id ? 'put' : 'post'](Routes.api.admin.services+(values.id ? `/${values.id}` : '' ), {
                id: values.id,
                title: values.title,
                slug: values.slug || '',
                icon: values.icon,
                details: values.details,
                content: values.content || '',
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
                    name="service"
                >
                    <Form.Item name="id" hidden>
                        <Input/>
                    </Form.Item>
                    <Form.Item
                        name="title"
                        label="Title"
                        rules={[
                            {
                                required: true,
                                message: 'Please input the title',
                            },
                        ]}
                    >
                        <Input placeholder="Enter Title"/>
                    </Form.Item>
                    <Form.Item
                        name="slug"
                        label="URL"
                        extra="Service page address: /services/your-url. Latin letters, digits and dashes. Leave empty to build it from the title."
                        rules={[
                            {
                                pattern: /^[a-z0-9-]*$/,
                                message: 'Use lowercase latin letters, digits and dashes',
                            },
                            {
                                max: 120,
                            },
                        ]}
                    >
                        <Input addonBefore="/services/" placeholder="web-development"/>
                    </Form.Item>
                    <Form.Item
                        name="icon"
                        label="Icon Class"
                        extra={<React.Fragment>Find your suitable icon: <a href="https://fontawesome.com/icons" target="_blank" rel="noreferrer">Font Awesome</a>|<a href="http://code.meta-platform.com/assets/mdi/preview.html" target="_blank" rel="noreferrer">Material Design</a></React.Fragment>}
                        rules={[
                            {
                                required: true,
                                message: 'Please input the icon class',
                            },
                        ]}
                    >
                        <Input placeholder="Enter Icon Class"/>
                    </Form.Item>
                    <Form.Item 
                        name="details" 
                        label="Details"
                        rules={[
                            {
                                required: true,
                                message: 'Please input the details',
                            },
                        ]}
                    >
                        <Input.TextArea rows={4} placeholder="Enter Details"/>
                    </Form.Item>
                    <Form.Item
                        name="content"
                        label="Page text"
                        extra="Shown only on the service page, under the short details. Separate paragraphs with an empty line. A line starting with &quot;## &quot; is a subheading, lines starting with &quot;- &quot; become a list."
                    >
                        <Input.TextArea rows={12} placeholder="What is included, how the work goes, terms, prices..."/>
                    </Form.Item>
                </Form>
            </Spin>
        </StyledDrawer>
    )
}

Service.propTypes = {
    handleCancel: PropTypes.func.isRequired,
    submitCallback: PropTypes.func.isRequired,
    visible: PropTypes.bool.isRequired,
    itemToEdit: PropTypes.object,
    loading: PropTypes.bool,
    componentLoading: PropTypes.bool,
    title: PropTypes.node,
}

export default Service;