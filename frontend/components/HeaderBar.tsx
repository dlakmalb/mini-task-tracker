'use client';

import { Layout, Typography } from 'antd';
import { FaTasks } from 'react-icons/fa';

const { Header } = Layout;
const { Title, Text } = Typography;

const HeaderBar = () => (
  <Header
    style={{
      background: '#eef5ff',
      padding: '12px 16px',
      borderBottom: '1px solid rgba(5, 5, 5, 0.06)',
    }}
  >
    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
      <FaTasks size={18} />
      <Title level={4} style={{ margin: 0 }}>
        Mini Task Tracker
      </Title>
    </div>
    <Text type="secondary">Track projects and tasks in one place.</Text>
  </Header>
);

export default HeaderBar;
