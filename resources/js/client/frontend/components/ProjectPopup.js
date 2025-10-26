import React, { useEffect, useState } from 'react';
import { Drawer, Button, Spin, Divider, Carousel, Row, Col, Image, Tag } from 'antd';
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
                            JSON.parse(props.project.categories).map((category, index) => (
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