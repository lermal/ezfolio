import React, { useEffect, useState } from 'react';
import { Drawer, Button, Spin, Divider, Carousel, Row, Col, Image, Tag, Space } from 'antd';
import styled from 'styled-components';
import PropTypes from 'prop-types';
import Utils from '../../common/helpers/Utils';

const StyledDrawer = styled(Drawer)`
    .ant-drawer-content-wrapper {
        width: 520px !important;
        @media (max-width: 768px) {
            max-width: calc(100vw - 16px) !important;
        }
    }
`;

const StyledTitle = styled.p`
    display: block;
    margin-bottom: 16px;
    color: rgba(0, 0, 0, 0.85);
    font-size: 16px;
    line-height: 1.5715;
    margin-bottom: 16px;
`;

const inkFor = (hex) => {
    const channels = hex.replace('#', '').match(/.{2}/g).map((pair) => {
        const value = parseInt(pair, 16) / 255;

        return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
    });
    const luminance = 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];

    return (luminance + 0.05) / 0.05 >= 1.05 / (luminance + 0.05) ? '#0b0c0e' : '#ffffff';
};

const buttonsOf = (project) => {
    if (!project || !project.buttons) {
        return [];
    }

    let buttons = project.buttons;
    if (typeof buttons === 'string') {
        try {
            buttons = JSON.parse(buttons);
        } catch (error) {
            return [];
        }
    }

    if (!Array.isArray(buttons)) {
        return [];
    }

    return buttons.filter((button) => (
        button
        && typeof button.label === 'string'
        && button.label.trim() !== ''
        && typeof button.url === 'string'
        && /^https?:\/\//i.test(button.url)
        && /^#[0-9a-f]{6}$/i.test(button.color || '')
    )).map((button) => ({
        label: button.label,
        url: button.url,
        color: button.color,
        ink: inkFor(button.color),
    }));
};

const ProjectPopup = (props) => {
    const [visible, setVisible] = useState(false);
    const [componentLoading, setComponentLoading] = useState((typeof props.componentLoading !== 'undefined') ? props.componentLoading : false);

    useEffect(() => {
        setTimeout(() => {
            setVisible(props.visible);
        }, 100);
    }, [props.visible])

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

    return (
        <StyledDrawer
            zIndex={99999}
            title={props.title}
            onClose={handleClose}
            visible={visible}
            destroyOnClose={true}
            maskClosable={true}
            forceRender={true}
            footer={
                <div
                    style={{
                        textAlign: 'right',
                    }}
                >
                    <Button disabled={componentLoading} onClick={handleClose} style={{ marginRight: 8 }}>
                        {props.translations?.close || 'Close'}
                    </Button>
                </div>
            }
        >
            <Spin spinning={componentLoading} size="large" delay={500}>
                <StyledTitle>{props.translations?.images || 'Images'}</StyledTitle>
                <Row>
                    <Col span={24}>
                        <Carousel autoplay pauseOnHover={false}>
                            {
                                JSON.parse(props.project.images).map((image, index) => (
                                    <div key={index}>
                                        <Image
                                            src={Utils.backend + '/' + image}
                                            preview={{
                                                mask: <div style={{color: 'white'}}>{props.translations?.preview || 'Просмотр'}</div>,
                                                zIndex: 99999999
                                            }}
                                            width='100%'
                                            placeholder={true}
                                            style={{
                                                maxHeight: '230px',
                                                transition: '0.3s ease',
                                                objectFit: 'cover'
                                            }}
                                            alt={props.project.title ? `${props.project.title} - изображение ${index + 1}` : `Изображение проекта ${index + 1}`}
                                            loading="lazy"
                                        />
                                    </div>
                                ))
                            }
                        </Carousel>
                    </Col>
                </Row>
                <Divider />
                <StyledTitle>{props.translations?.category || 'Category'}</StyledTitle>
                <Row>
                    <Col span={24}>
                        {
                            Utils.parseList(props.project.categories).map((category, index) => (
                                <Tag key={index} style={{background: 'var(--z-accent-color)', color: 'white', textTransform: 'capitalize'}}>{category}</Tag>
                            ))
                        }
                    </Col>
                </Row>
                {
                    (props.project.details !== null) && props.project.details !== '' && (
                        <React.Fragment>
                            <Divider/>
                            <StyledTitle>{props.translations?.description || 'Description'}</StyledTitle>
                            <Row>
                                <Col span={24}>
                                    {props.project.details}
                                </Col>
                            </Row>
                        </React.Fragment>
                    )
                }
                {
                    buttonsOf(props.project).length > 0 && (
                        <React.Fragment>
                            <Divider/>
                            <StyledTitle>{props.translations?.link || 'Link'}</StyledTitle>
                            <Space wrap>
                                {buttonsOf(props.project).map((button, index) => (
                                    <a
                                        key={index}
                                        href={button.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        style={{
                                            display: 'inline-block',
                                            padding: '6px 14px',
                                            borderRadius: 6,
                                            background: button.color,
                                            color: button.ink,
                                        }}
                                    >
                                        {button.label}
                                    </a>
                                ))}
                            </Space>
                        </React.Fragment>
                    )
                }
                {
                    props.project.link && props.project.link !== '' && (
                        <React.Fragment>
                            <Divider/>
                            <StyledTitle>{props.translations?.link || 'Link'}</StyledTitle>
                            <Row>
                                <Col span={24}>
                                    <a href={props.project.link} target="_blank" rel="noreferrer">
                                        {props.project.link}
                                    </a>
                                </Col>
                            </Row>
                        </React.Fragment>
                    )
                }
            </Spin>
        </StyledDrawer>
    )
}

ProjectPopup.propTypes = {
    handleCancel: PropTypes.func.isRequired,
    visible: PropTypes.bool.isRequired,
    project: PropTypes.object,
    componentLoading: PropTypes.bool,
    title: PropTypes.node,
    translations: PropTypes.object,
}

export default ProjectPopup;