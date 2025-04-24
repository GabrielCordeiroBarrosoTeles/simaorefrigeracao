import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { Fan, Thermometer, PenToolIcon as Tool, Phone, Snowflake, FileText, ArrowRight } from "lucide-react"
import Image from "next/image"
import Link from "next/link"

export default function Home() {
  return (
    <div className="flex min-h-screen flex-col">
      <header className="sticky top-0 z-50 w-full border-b bg-white">
        <div className="container flex h-16 items-center justify-between">
          <div className="flex items-center gap-2">
            <Snowflake className="h-6 w-6 text-blue-600" />
            <span className="text-xl font-bold text-blue-600">FrioCerto</span>
          </div>
          <nav className="hidden md:flex gap-6">
            <Link href="#inicio" className="text-sm font-medium hover:text-blue-600">
              Início
            </Link>
            <Link href="#servicos" className="text-sm font-medium hover:text-blue-600">
              Serviços
            </Link>
            <Link href="#sobre" className="text-sm font-medium hover:text-blue-600">
              Sobre
            </Link>
            <Link href="#contato" className="text-sm font-medium hover:text-blue-600">
              Contato
            </Link>
          </nav>
          <Button className="bg-blue-600 hover:bg-blue-700">
            <Phone className="mr-2 h-4 w-4" /> Contato
          </Button>
        </div>
      </header>
      <main className="flex-1">
        <section id="inicio" className="relative bg-gradient-to-r from-blue-600 to-blue-800 py-20 text-white">
          <div className="container grid gap-8 md:grid-cols-2 items-center">
            <div className="space-y-6">
              <h1 className="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl">
                Soluções completas em ar condicionado
              </h1>
              <p className="text-lg text-blue-100">
                Oferecemos serviços de instalação, manutenção e projetos para garantir o conforto térmico ideal para sua
                casa ou empresa.
              </p>
              <div className="flex flex-col sm:flex-row gap-4">
                <Button size="lg" className="bg-white text-blue-600 hover:bg-blue-50">
                  Solicitar orçamento
                </Button>
                <Button size="lg" variant="outline" className="border-white text-white hover:bg-blue-700">
                  Nossos serviços <ArrowRight className="ml-2 h-4 w-4" />
                </Button>
              </div>
            </div>
            <div className="hidden md:block relative h-[400px]">
              <Image
                src="/placeholder.svg?height=400&width=500"
                alt="Técnico instalando ar condicionado"
                fill
                className="object-cover rounded-lg"
              />
            </div>
          </div>
          <div className="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent" />
        </section>

        <section id="servicos" className="py-20">
          <div className="container">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold tracking-tight sm:text-4xl text-blue-600">Nossos Serviços</h2>
              <p className="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
                Oferecemos soluções completas para climatização, desde a instalação até a manutenção.
              </p>
            </div>
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              <Card className="border-blue-100 hover:border-blue-300 transition-colors">
                <CardHeader>
                  <Fan className="h-10 w-10 text-blue-600 mb-2" />
                  <CardTitle>Instalação de Ar Condicionado</CardTitle>
                  <CardDescription>Instalação profissional de equipamentos residenciais e comerciais.</CardDescription>
                </CardHeader>
                <CardContent>
                  <ul className="list-disc pl-5 space-y-1 text-gray-600">
                    <li>Instalação de splits e multi-splits</li>
                    <li>Instalação de ar condicionado central</li>
                    <li>Instalação de VRF/VRV</li>
                  </ul>
                </CardContent>
              </Card>
              <Card className="border-blue-100 hover:border-blue-300 transition-colors">
                <CardHeader>
                  <Thermometer className="h-10 w-10 text-blue-600 mb-2" />
                  <CardTitle>Manutenção Preventiva</CardTitle>
                  <CardDescription>
                    Serviços regulares para garantir o funcionamento ideal do seu equipamento.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ul className="list-disc pl-5 space-y-1 text-gray-600">
                    <li>Limpeza de filtros e componentes</li>
                    <li>Verificação de gás refrigerante</li>
                    <li>Inspeção de componentes elétricos</li>
                  </ul>
                </CardContent>
              </Card>
              <Card className="border-blue-100 hover:border-blue-300 transition-colors">
                <CardHeader>
                  <Tool className="h-10 w-10 text-blue-600 mb-2" />
                  <CardTitle>Manutenção Corretiva</CardTitle>
                  <CardDescription>
                    Reparo rápido e eficiente para resolver problemas no seu equipamento.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ul className="list-disc pl-5 space-y-1 text-gray-600">
                    <li>Diagnóstico preciso de falhas</li>
                    <li>Reparo de vazamentos</li>
                    <li>Substituição de componentes</li>
                  </ul>
                </CardContent>
              </Card>
              <Card className="border-blue-100 hover:border-blue-300 transition-colors">
                <CardHeader>
                  <Phone className="h-10 w-10 text-blue-600 mb-2" />
                  <CardTitle>Visita Técnica</CardTitle>
                  <CardDescription>
                    Avaliação profissional para identificar problemas e propor soluções.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ul className="list-disc pl-5 space-y-1 text-gray-600">
                    <li>Diagnóstico de problemas</li>
                    <li>Orçamento detalhado</li>
                    <li>Recomendações técnicas</li>
                  </ul>
                </CardContent>
              </Card>
              <Card className="border-blue-100 hover:border-blue-300 transition-colors">
                <CardHeader>
                  <Snowflake className="h-10 w-10 text-blue-600 mb-2" />
                  <CardTitle>Câmara Frigorífica</CardTitle>
                  <CardDescription>Soluções para armazenamento refrigerado comercial e industrial.</CardDescription>
                </CardHeader>
                <CardContent>
                  <ul className="list-disc pl-5 space-y-1 text-gray-600">
                    <li>Instalação de câmaras frigoríficas</li>
                    <li>Manutenção de sistemas de refrigeração</li>
                    <li>Projetos personalizados</li>
                  </ul>
                </CardContent>
              </Card>
              <Card className="border-blue-100 hover:border-blue-300 transition-colors">
                <CardHeader>
                  <FileText className="h-10 w-10 text-blue-600 mb-2" />
                  <CardTitle>Projetos</CardTitle>
                  <CardDescription>
                    Desenvolvimento de projetos de climatização para diversos ambientes.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ul className="list-disc pl-5 space-y-1 text-gray-600">
                    <li>Projetos para residências</li>
                    <li>Projetos para comércios</li>
                    <li>Projetos para indústrias</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
          </div>
        </section>

        <section id="sobre" className="py-20 bg-blue-50">
          <div className="container">
            <div className="grid gap-12 md:grid-cols-2 items-center">
              <div className="relative h-[400px]">
                <Image
                  src="/placeholder.svg?height=400&width=500"
                  alt="Nossa equipe"
                  fill
                  className="object-cover rounded-lg"
                />
              </div>
              <div className="space-y-6">
                <h2 className="text-3xl font-bold tracking-tight sm:text-4xl text-blue-600">Sobre a FrioCerto</h2>
                <p className="text-lg text-gray-600">
                  Somos uma empresa especializada em soluções de climatização, com anos de experiência no mercado. Nossa
                  equipe é formada por profissionais qualificados e comprometidos com a excelência.
                </p>
                <div className="grid grid-cols-2 gap-4">
                  <div className="bg-white p-4 rounded-lg shadow-sm">
                    <div className="text-3xl font-bold text-blue-600">10+</div>
                    <div className="text-gray-600">Anos de experiência</div>
                  </div>
                  <div className="bg-white p-4 rounded-lg shadow-sm">
                    <div className="text-3xl font-bold text-blue-600">500+</div>
                    <div className="text-gray-600">Clientes satisfeitos</div>
                  </div>
                  <div className="bg-white p-4 rounded-lg shadow-sm">
                    <div className="text-3xl font-bold text-blue-600">1000+</div>
                    <div className="text-gray-600">Projetos realizados</div>
                  </div>
                  <div className="bg-white p-4 rounded-lg shadow-sm">
                    <div className="text-3xl font-bold text-blue-600">24h</div>
                    <div className="text-gray-600">Atendimento</div>
                  </div>
                </div>
                <Button className="bg-blue-600 hover:bg-blue-700">Conheça nossa história</Button>
              </div>
            </div>
          </div>
        </section>

        <section id="depoimentos" className="py-20">
          <div className="container">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold tracking-tight sm:text-4xl text-blue-600">
                O que nossos clientes dizem
              </h2>
              <p className="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
                A satisfação dos nossos clientes é nossa prioridade.
              </p>
            </div>
            <div className="grid gap-6 md:grid-cols-3">
              <Card className="border-blue-100">
                <CardContent className="pt-6">
                  <div className="flex items-center gap-4 mb-4">
                    <div className="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                      <span className="text-blue-600 font-bold">JC</span>
                    </div>
                    <div>
                      <h4 className="font-medium">João Carlos</h4>
                      <p className="text-sm text-gray-500">Residencial</p>
                    </div>
                  </div>
                  <p className="text-gray-600">
                    "Excelente serviço! A equipe foi pontual, profissional e deixou tudo limpo após a instalação. O ar
                    condicionado está funcionando perfeitamente."
                  </p>
                </CardContent>
              </Card>
              <Card className="border-blue-100">
                <CardContent className="pt-6">
                  <div className="flex items-center gap-4 mb-4">
                    <div className="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                      <span className="text-blue-600 font-bold">MS</span>
                    </div>
                    <div>
                      <h4 className="font-medium">Maria Silva</h4>
                      <p className="text-sm text-gray-500">Comercial</p>
                    </div>
                  </div>
                  <p className="text-gray-600">
                    "Contratamos para a manutenção dos equipamentos da nossa loja e o resultado foi excelente. Recomendo
                    para todos que precisam de serviços de qualidade."
                  </p>
                </CardContent>
              </Card>
              <Card className="border-blue-100">
                <CardContent className="pt-6">
                  <div className="flex items-center gap-4 mb-4">
                    <div className="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                      <span className="text-blue-600 font-bold">RL</span>
                    </div>
                    <div>
                      <h4 className="font-medium">Roberto Lima</h4>
                      <p className="text-sm text-gray-500">Industrial</p>
                    </div>
                  </div>
                  <p className="text-gray-600">
                    "Projeto de climatização para nossa fábrica executado com perfeição. Equipe técnica altamente
                    qualificada e comprometida com prazos."
                  </p>
                </CardContent>
              </Card>
            </div>
          </div>
        </section>

        <section id="contato" className="py-20 bg-blue-600 text-white">
          <div className="container">
            <div className="grid gap-12 md:grid-cols-2">
              <div className="space-y-6">
                <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">Entre em contato</h2>
                <p className="text-lg text-blue-100">
                  Estamos prontos para atender suas necessidades de climatização. Entre em contato conosco para um
                  orçamento sem compromisso.
                </p>
                <div className="space-y-4">
                  <div className="flex items-center gap-4">
                    <div className="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                      <Phone className="h-5 w-5" />
                    </div>
                    <div>
                      <h4 className="font-medium">Telefone</h4>
                      <p>(11) 9999-9999</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="lucide lucide-mail"
                      >
                        <rect width="20" height="16" x="2" y="4" rx="2" />
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                      </svg>
                    </div>
                    <div>
                      <h4 className="font-medium">Email</h4>
                      <p>contato@friocerto.com.br</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="lucide lucide-map-pin"
                      >
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                        <circle cx="12" cy="10" r="3" />
                      </svg>
                    </div>
                    <div>
                      <h4 className="font-medium">Endereço</h4>
                      <p>Av. Principal, 1000 - São Paulo, SP</p>
                    </div>
                  </div>
                </div>
              </div>
              <div className="bg-white p-6 rounded-lg shadow-lg text-gray-900">
                <h3 className="text-xl font-bold mb-4 text-blue-600">Solicite um orçamento</h3>
                <form className="space-y-4">
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                      <label htmlFor="name" className="text-sm font-medium">
                        Nome
                      </label>
                      <Input id="name" placeholder="Seu nome" />
                    </div>
                    <div className="space-y-2">
                      <label htmlFor="email" className="text-sm font-medium">
                        Email
                      </label>
                      <Input id="email" type="email" placeholder="seu@email.com" />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <label htmlFor="phone" className="text-sm font-medium">
                      Telefone
                    </label>
                    <Input id="phone" placeholder="(00) 00000-0000" />
                  </div>
                  <div className="space-y-2">
                    <label htmlFor="service" className="text-sm font-medium">
                      Serviço
                    </label>
                    <select
                      id="service"
                      className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                      <option value="">Selecione um serviço</option>
                      <option value="instalacao">Instalação</option>
                      <option value="manutencao-preventiva">Manutenção Preventiva</option>
                      <option value="manutencao-corretiva">Manutenção Corretiva</option>
                      <option value="visita-tecnica">Visita Técnica</option>
                      <option value="camara-frigorifica">Câmara Frigorífica</option>
                      <option value="projetos">Projetos</option>
                    </select>
                  </div>
                  <div className="space-y-2">
                    <label htmlFor="message" className="text-sm font-medium">
                      Mensagem
                    </label>
                    <Textarea id="message" placeholder="Descreva sua necessidade" className="min-h-[100px]" />
                  </div>
                  <Button className="w-full bg-blue-600 hover:bg-blue-700">Enviar mensagem</Button>
                </form>
              </div>
            </div>
          </div>
        </section>
      </main>
      <footer className="bg-gray-900 text-white py-12">
        <div className="container">
          <div className="grid gap-8 md:grid-cols-4">
            <div>
              <div className="flex items-center gap-2 mb-4">
                <Snowflake className="h-6 w-6 text-blue-400" />
                <span className="text-xl font-bold text-blue-400">FrioCerto</span>
              </div>
              <p className="text-gray-400">
                Soluções completas em climatização para residências, comércios e indústrias.
              </p>
            </div>
            <div>
              <h3 className="text-lg font-medium mb-4">Serviços</h3>
              <ul className="space-y-2 text-gray-400">
                <li>Instalação de Ar Condicionado</li>
                <li>Manutenção Preventiva</li>
                <li>Manutenção Corretiva</li>
                <li>Visita Técnica</li>
                <li>Câmara Frigorífica</li>
                <li>Projetos</li>
              </ul>
            </div>
            <div>
              <h3 className="text-lg font-medium mb-4">Links Rápidos</h3>
              <ul className="space-y-2 text-gray-400">
                <li>
                  <Link href="#inicio" className="hover:text-blue-400">
                    Início
                  </Link>
                </li>
                <li>
                  <Link href="#servicos" className="hover:text-blue-400">
                    Serviços
                  </Link>
                </li>
                <li>
                  <Link href="#sobre" className="hover:text-blue-400">
                    Sobre
                  </Link>
                </li>
                <li>
                  <Link href="#depoimentos" className="hover:text-blue-400">
                    Depoimentos
                  </Link>
                </li>
                <li>
                  <Link href="#contato" className="hover:text-blue-400">
                    Contato
                  </Link>
                </li>
              </ul>
            </div>
            <div>
              <h3 className="text-lg font-medium mb-4">Contato</h3>
              <ul className="space-y-2 text-gray-400">
                <li className="flex items-center gap-2">
                  <Phone className="h-4 w-4 text-blue-400" /> (11) 9999-9999
                </li>
                <li className="flex items-center gap-2">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="lucide lucide-mail text-blue-400"
                  >
                    <rect width="20" height="16" x="2" y="4" rx="2" />
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                  </svg>{" "}
                  contato@friocerto.com.br
                </li>
                <li className="flex items-center gap-2">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="lucide lucide-map-pin text-blue-400"
                  >
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                    <circle cx="12" cy="10" r="3" />
                  </svg>{" "}
                  Av. Principal, 1000 - São Paulo, SP
                </li>
              </ul>
              <div className="flex gap-4 mt-4">
                <a
                  href="#"
                  className="h-10 w-10 rounded-full bg-blue-900 flex items-center justify-center hover:bg-blue-800"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="lucide lucide-facebook"
                  >
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                  </svg>
                </a>
                <a
                  href="#"
                  className="h-10 w-10 rounded-full bg-blue-900 flex items-center justify-center hover:bg-blue-800"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="lucide lucide-instagram"
                  >
                    <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                    <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                  </svg>
                </a>
                <a
                  href="#"
                  className="h-10 w-10 rounded-full bg-blue-900 flex items-center justify-center hover:bg-blue-800"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="lucide lucide-linkedin"
                  >
                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" />
                    <rect width="4" height="12" x="2" y="9" />
                    <circle cx="4" cy="4" r="2" />
                  </svg>
                </a>
              </div>
            </div>
          </div>
          <div className="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>© 2023 FrioCerto. Todos os direitos reservados.</p>
          </div>
        </div>
      </footer>
    </div>
  )
}
