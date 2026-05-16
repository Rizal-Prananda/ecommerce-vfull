import { PrismaClient } from '@prisma/client';

type PrismaGlobal = typeof globalThis & {
	__prisma?: PrismaClient;
};

const g = globalThis as PrismaGlobal;

export const prisma =
	g.__prisma ??
	new PrismaClient({
		log: []
	});

if (import.meta.env.DEV) g.__prisma = prisma;
