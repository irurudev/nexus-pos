import type { ReactNode } from 'react';
import { useState, useRef, useEffect } from 'react';
import {
  Box,
  Flex,
  Icon,
  Text,
  VStack,
  HStack,
  Avatar,
  IconButton,
} from '@chakra-ui/react';
import {
  FiHome,
  FiShoppingCart,
  FiPackage,
  FiUsers,
  FiFolder,
  FiMenu,
  FiLogOut,
  FiUser,
  FiChevronDown,
} from 'react-icons/fi';
import { Link as RouterLink, useLocation, useNavigate, type Location } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import usePermissions from '../hooks/usePermissions';
import type { IconType } from 'react-icons';

interface MenuItem {
  name: string;
  path: string;
  icon: IconType;
}

const menuItems: MenuItem[] = [
  { name: 'Dashboard', path: '/dashboard', icon: FiHome },
  { name: 'Penjualan', path: '/penjualan', icon: FiShoppingCart },
  { name: 'Barang', path: '/barang', icon: FiPackage },
  { name: 'Kategori', path: '/kategori', icon: FiFolder },
  { name: 'Pelanggan', path: '/pelanggan', icon: FiUsers },
];

interface DashboardLayoutProps {
  children: ReactNode;
}

interface SidebarContentProps {
  collapsed: boolean;
  location: Location;
}

const SidebarContent = ({ collapsed, location }: SidebarContentProps) => {
  const perms = usePermissions();
  const items = [...menuItems];
  if (perms.role === 'admin') {
    items.push({ name: 'Audit Logs', path: '/audit-logs', icon: FiFolder });
  }

  return (
    <VStack h="full" p={collapsed ? 2 : 4} gap={2} align="stretch" overflowY="auto">
      {/* Logo */}
      <HStack mb={collapsed ? 4 : 6} justify={collapsed ? "center" : "center"}>
        <Icon as={FiShoppingCart} fontSize={collapsed ? "xl" : "2xl"} color="brand.500" />
        {!collapsed && (
          <Text fontSize="lg" fontWeight="bold" color="gray.800">
            POS System
          </Text>
        )}
      </HStack>

      {/* Menu Items */}
      {items.map((item) => {
        const isActive = location.pathname === item.path;
        return (
          <RouterLink
            key={item.path}
            to={item.path}
            style={{ textDecoration: 'none' }}
          >
            <Box
              rounded="md"
              p={collapsed ? 2 : 3}
              display="flex"
              alignItems="center"
              justifyContent={collapsed ? "center" : "flex-start"}
              gap={collapsed ? 0 : 3}
              transition="all 0.2s"
              bg={isActive ? 'brand.50' : 'transparent'}
              color={isActive ? 'brand.600' : 'gray.700'}
              fontWeight={isActive ? 'semibold' : 'normal'}
              borderLeft={isActive ? '4px solid' : 'none'}
              borderColor={isActive ? 'brand.500' : 'transparent'}
              pl={isActive ? (collapsed ? '1' : '2') : (collapsed ? '2' : '3')}
              _hover={{
                bg: 'gray.100',
                color: 'brand.600',
              }}
              cursor="pointer"
            >
              <Icon as={item.icon} boxSize={collapsed ? 4 : 5} />
              {!collapsed && <Text fontSize="sm">{item.name}</Text>}
            </Box>
          </RouterLink>
        );
      })}
    </VStack>
  );
};

export default function DashboardLayout({ children }: DashboardLayoutProps) {
  const [isSidebarCollapsed, setSidebarCollapsed] = useState(false);
  const { user, logout } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const [showUserMenu, setShowUserMenu] = useState(false);
  const userMenuRef = useRef<HTMLDivElement | null>(null);
  const userMenuTriggerRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    function handleOutside(e: MouseEvent) {
      const target = e.target as Node;
      if (!userMenuRef.current || !userMenuTriggerRef.current) return;
      if (!userMenuRef.current.contains(target) && !userMenuTriggerRef.current.contains(target)) {
        setShowUserMenu(false);
      }
    }
    function handleKey(e: KeyboardEvent) {
      if (e.key === 'Escape') setShowUserMenu(false);
    }
    document.addEventListener('mousedown', handleOutside);
    document.addEventListener('keydown', handleKey);
    return () => {
      document.removeEventListener('mousedown', handleOutside);
      document.removeEventListener('keydown', handleKey);
    };
  }, []);

  const handleLogout = async () => {
    try {
      await logout();
      navigate('/login');
    } catch (error) {
      console.error('Logout error:', error);
    }
  }; 

  return (
    <Flex w="100vw" h="100vh" overflow="hidden">
      {/* Sidebar */}
      <Box
        w={{
          base: isSidebarCollapsed ? '60px' : '200px',
          sm: isSidebarCollapsed ? '60px' : '220px',
          md: isSidebarCollapsed ? '60px' : '240px',
          lg: isSidebarCollapsed ? '60px' : '260px'
        }}
        minW={{
          base: isSidebarCollapsed ? '60px' : '200px',
          sm: isSidebarCollapsed ? '60px' : '220px',
          md: isSidebarCollapsed ? '60px' : '240px',
          lg: isSidebarCollapsed ? '60px' : '260px'
        }}
        bg="white"
        borderRight="1px"
        borderColor="gray.200"
        boxShadow="sm"
        zIndex={20}
        transition="all 0.3s ease"
      >
        <SidebarContent collapsed={isSidebarCollapsed} location={location} />
      </Box>

      {/* Main Content */}
      <Box flex={1} display="flex" flexDirection="column" overflow="hidden">
        {/* Top Navigation */}
         <Flex
           h={{ base: '52px', sm: '56px', md: '60px' }}
           bg="white"
           borderBottom="1px"
           borderColor="gray.200"
           px={{ base: 4, md: 6, lg: 8 }}
           alignItems="center"
           justifyContent="space-between"
           boxShadow="sm"
         >
           <IconButton
             onClick={() => setSidebarCollapsed(!isSidebarCollapsed)}
             variant="ghost"
             aria-label="Toggle sidebar"
             size="lg"
           >
             <FiMenu size={20} />
           </IconButton>

          <Box flex={1} />

          {/* User Menu */}
          <HStack gap={4} position="relative">
            <Box
              ref={userMenuTriggerRef}
              as="button"
              onClick={() => setShowUserMenu((v) => !v)}
              display="flex"
              alignItems="center"
              gap={2}
              p={2}
              rounded="md"
              _hover={{ bg: 'gray.100' }}
              aria-haspopup="true"
              aria-expanded={showUserMenu}
              tabIndex={0}
              onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                  e.preventDefault();
                  setShowUserMenu((v) => !v);
                }
              }}
            >
              <Avatar.Root size="sm" bg="brand.500">
                <Avatar.Fallback>{user?.name?.substring(0, 2).toUpperCase()}</Avatar.Fallback>
              </Avatar.Root>
              <VStack gap={0} align="flex-start" display={{ base: 'none', md: 'flex' }}>
                <Text fontSize="sm" fontWeight="semibold" color="gray.900">
                  {user?.name}
                </Text>
                <Text fontSize="xs" color="gray.900">
                  {user?.role}
                </Text>
              </VStack>
              <Icon as={FiChevronDown} boxSize={4} />
            </Box>

            {/* Dropdown Menu */}
            {showUserMenu && (
              <VStack
                ref={userMenuRef}
                position="absolute"
                top="100%"
                right={0}
                mt={1}
                bg="white"
                border="1px"
                borderColor="gray.200"
                rounded="md"
                boxShadow="md"
                overflow="hidden"
                zIndex={10}
                gap={0}
                minW="160px"
              >
                <Box
                  as="button"
                  w="full"
                  p={3}
                  textAlign="left"
                  fontSize="sm"
                  display="flex"
                  alignItems="center"
                  gap={2}
                  color="gray.900"
                  _hover={{ bg: 'gray.100' }}
                  onClick={() => { navigate('/profile'); setShowUserMenu(false); }}
                >
                  <Icon as={FiUser} boxSize={4} color="gray.900" />
                  Profile
                </Box>
                <Box
                  as="button"
                  w="full"
                  p={3}
                  textAlign="left"
                  fontSize="sm"
                  display="flex"
                  alignItems="center"
                  gap={2}
                  borderTop="1px"
                  borderColor="gray.200"
                  onClick={handleLogout}
                  color="gray.900"
                  _hover={{ bg: 'red.50', color: 'red.600' }}
                >
                  <Icon as={FiLogOut} boxSize={4} color="gray.900" />
                  Logout
                </Box>
              </VStack>
            )}
          </HStack>
        </Flex>

        {/* Page Content */}
        <Box
          flex={1}
          overflow="auto"
          bg="gray.50"
          px={{ base: 4, md: 6, lg: 8 }}
          py={6}
          w="full"
        >
          <Box w="full" mx="auto">{children}</Box>
        </Box>
      </Box>
    </Flex>
  );
}