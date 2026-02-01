import { useState } from 'react';
import {
  Box,
  Button,
  Input,
  VStack,
  Heading,
  Text,
  Card,
  HStack,
  Icon,
  createToaster,
  Separator,
  Field,
} from '@chakra-ui/react';
import { PasswordInput } from '../../components/ui/password-input';
import { FiShoppingCart } from 'react-icons/fi';
import { useAuth } from '../../context/AuthContext';
import { useNavigate } from 'react-router-dom';

const toaster = createToaster({
  placement: 'top-end',
});

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();
  const currentYear = new Date().getFullYear();

  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const handleSubmit = async (e?: React.FormEvent) => {
    e?.preventDefault();
    setIsLoading(true);
    setErrorMessage(null);

    try {
      await login(email, password);
      toaster.create({
        title: 'Login Berhasil',
        description: 'Selamat datang kembali!',
        type: 'success',
      });
      navigate('/dashboard');
    } catch (error: any) {
      const status = error.response?.status;
      const serverMsg = error.response?.data?.message;
      if (status === 403) {
        const msg = serverMsg || 'Akun belum aktif atau dinonaktifkan';
        setErrorMessage(msg);
        toaster.create({
          title: 'Akun Dinonaktifkan',
          description: msg,
          type: 'error',
        });
      } else {
        const msg = serverMsg || 'Email atau password salah';
        setErrorMessage(msg);
        toaster.create({
          title: 'Login Gagal',
          description: msg,
          type: 'error',
        });
      }
    } finally {
      setIsLoading(false);
    }
  };

  // Helper untuk quick login: langsung panggil login dengan kredensial yang diberikan
  // agar tidak bergantung pada pembaruan state asinkron dan menghindari body kosong.
  const quickLogin = async (e: string, p: string) => {
    setEmail(e);
    setPassword(p);

    setIsLoading(true);
    setErrorMessage(null);
    try {
      await login(e, p);
      toaster.create({
        title: 'Login Berhasil',
        description: 'Selamat datang kembali!',
        type: 'success',
      });
      navigate('/dashboard');
    } catch (error: any) {
      const status = error.response?.status;
      const serverMsg = error.response?.data?.message;
      if (status === 403) {
        const msg = serverMsg || 'Akun belum aktif atau dinonaktifkan';
        setErrorMessage(msg);
        toaster.create({
          title: 'Akun Dinonaktifkan',
          description: msg,
          type: 'error',
        });
      } else {
        const msg = serverMsg || 'Email atau password salah';
        setErrorMessage(msg);
        toaster.create({
          title: 'Login Gagal',
          description: msg,
          type: 'error',
        });
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Box 
      minH="100vh" 
      minW="100vw"
      display="flex" 
      alignItems="center" 
      justifyContent="center" 
      p={4} 
      bgGradient="to-br" 
      gradientFrom="gray.50" 
      gradientTo="gray.200"
    >
      <Card.Root maxW="1000px" w="full" boxShadow="2xl" border="none" overflow="hidden">
        <Card.Body p={0}>
          <HStack align="stretch" gap={0}>
            
            {/* SISI KIRI: Branding & Info */}
            <Box 
              display={{ base: 'none', md: 'flex' }} 
              bg="blue.600" 
              color="white" 
              w={{ md: '45%' }} 
              p={12}
              position="relative"
              overflow="hidden"
            >
              {/* Dekorasi Background */}
              <Box 
                position="absolute" 
                top="-10%" 
                right="-10%" 
                bg="blue.500" 
                w="300px" 
                h="300px" 
                rounded="full" 
                opacity={0.5} 
              />
              
              <VStack align="start" gap={8} h="100%" zIndex={1}>
                <HStack gap={3}>
                  <Icon as={FiShoppingCart} boxSize={10} />
                  <Heading size="2xl" letterSpacing="tight">NexusPOS</Heading>
                </HStack>
                
                <VStack align="start" gap={4}>
                  <Heading size="lg">Efisiensi dalam genggaman anda.</Heading>
                  <Text fontSize="lg" color="blue.100">
                    Sistem kasir pintar untuk membantu pertumbuhan bisnis Anda secara real-time.
                  </Text>
                </VStack>

                <Box mt="auto" w="full" p={4} bg="whiteAlpha.200" rounded="lg" backdropFilter="blur(10px)">
                  <Text fontSize="xs" fontWeight="bold" mb={2} textTransform="uppercase" letterSpacing="widest">
                    Akses Demo
                  </Text>
                  <HStack justify="space-between" fontSize="sm">
                    <Text>Admin: admin@example.com</Text>
                    <Separator orientation="vertical" h="10px" />
                    <Text>Pass: password</Text>
                  </HStack>
                </Box>
              </VStack>
            </Box>

            {/* SISI KANAN: Login Form */}
            <Box w={{ base: 'full', md: '55%' }} p={{ base: 8, md: 16 }} bg="white">
              <VStack align="stretch" gap={8}>
                <Box
                  w="full"
                  minH="56px"
                  p={3}
                  borderRadius="md"
                  borderWidth="1px"
                  borderColor={errorMessage ? 'red.200' : 'transparent'}
                  bg={errorMessage ? 'red.50' : 'transparent'}
                  display="flex"
                  alignItems="center"
                  justifyContent="center"
                  role={errorMessage ? 'alert' : undefined}
                >
                  <Text color={errorMessage ? 'red.600' : 'transparent'} fontSize="sm" fontWeight="semibold">
                    {errorMessage || '\u00A0'}
                  </Text>
                </Box>

                <VStack align="start" gap={2}>
                  <Heading size="xl" color="gray.800">Selamat Datang</Heading>
                  <Text color="gray.500">Silakan masuk dengan akun Anda</Text>
                </VStack>

                <form onSubmit={handleSubmit}>
                  <VStack gap={5}>
                    <Field.Root required>
                      <Field.Label fontWeight="bold">Email Bisnis</Field.Label>
                      <Input
                        aria-label="Email bisnis"
                        required
                        placeholder="nama@perusahaan.com"
                        variant="subtle"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        _focus={{ bg: 'white', borderColor: 'blue.500' }}
                      />
                    </Field.Root>

                    <Field.Root required>
                      <HStack justify="space-between" w="full">
                        <Field.Label fontWeight="bold">Password</Field.Label>
                        <Text as="a" fontSize="xs" color="blue.600" fontWeight="semibold" cursor="pointer">
                          Lupa password?
                        </Text>
                      </HStack>
                      <PasswordInput
                        aria-label="Password"
                        required
                        placeholder="••••••••"
                        variant="subtle"
                        value={password}
                        onChange={(e: any) => setPassword(e.target.value)}
                        _focus={{ bg: 'white', borderColor: 'blue.500' }}
                      />
                    </Field.Root>

                    <Button
                      type="submit"
                      colorScheme="blue"
                      size="lg"
                      w="full"
                      loading={isLoading}
                      boxShadow="0 4px 12px rgba(49, 130, 206, 0.3)"
                      aria-label="Masuk sekarang"
                    >
                      Masuk
                    </Button>
                  </VStack>
                </form>

                <HStack>
                  <Separator />
                  <Text textStyle="xs" whiteSpace="nowrap" color="gray.400" px={2}>ATAU LOGIN CEPAT</Text>
                  <Separator />
                </HStack>

                <HStack gap={4}>
                  <Button flex={1} variant="outline" borderColor="gray.200" size="sm" onClick={() => quickLogin('admin@example.com', 'password')} aria-label="Login sebagai Admin">
                    Admin
                  </Button>
                  <Button flex={1} variant="outline" borderColor="gray.200" size="sm" onClick={() => quickLogin('fakhirul@example.com', 'password')} aria-label="Login sebagai Kasir">
                    Kasir
                  </Button>
                </HStack>

                <Text fontSize="xs" color="gray.400" textAlign="center">
                  © {currentYear} NexusPOS System. Seluruh hak cipta dilindungi.
                </Text>
              </VStack>
            </Box>
          </HStack>
        </Card.Body>
      </Card.Root>
    </Box>
  );
}