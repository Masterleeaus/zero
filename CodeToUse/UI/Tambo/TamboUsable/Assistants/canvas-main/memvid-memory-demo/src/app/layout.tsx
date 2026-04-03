import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import brand from '../../brand.json';

const inter = Inter({ subsets: ['latin'] });

export const metadata: Metadata = {
  title: brand.name,
  description: brand.tagline,
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="dark">
      <body className={inter.className}>
        {children}
      </body>
    </html>
  );
}
